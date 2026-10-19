"""
API integration tests for Fauri AI Service endpoints.
"""

import io
import wave
import struct
import pytest
from fastapi.testclient import TestClient
from app.main import app

client = TestClient(app)

def generate_test_wav_bytes(duration_sec: float = 1.0, sample_rate: int = 16000) -> bytes:
    """Generates a small valid mono 16kHz PCM WAV in-memory."""
    buf = io.BytesIO()
    num_samples = int(duration_sec * sample_rate)
    with wave.open(buf, "wb") as wf:
        wf.setnchannels(1)
        wf.setsampwidth(2)
        wf.setframerate(sample_rate)
        # 440 Hz gentle tone
        import math
        raw_data = bytearray()
        for i in range(num_samples):
            value = int(32767.0 * 0.1 * math.sin(2.0 * math.pi * 440.0 * i / sample_rate))
            raw_data.extend(struct.pack("<h", value))
        wf.writeframes(raw_data)
    buf.seek(0)
    return buf.read()

def test_root():
    response = client.get("/")
    assert response.status_code == 200
    data = response.json()
    assert data["service"] == "Fauri AI Service"
    assert "/health" in data["endpoints"]

def test_health_check():
    response = client.get("/health")
    assert response.status_code == 200
    data = response.json()
    assert data["status"] == "ok"
    assert "models" in data

def test_classify_roman_urdu():
    response = client.post("/classify", json={"text": "bhai mujhe ye black shirt medium size me order karni hai COD pe"})
    assert response.status_code == 200
    data = response.json()
    assert "category" in data
    assert "urgency" in data
    assert "summary" in data
    assert "confidence" in data
    assert isinstance(data["confidence"], float)

def test_classify_urdu_script():
    response = client.post("/classify", json={"text": "براہ کرم میرا آرڈر کینسل کر دیں اور پیسے واپس بھیجیں"})
    assert response.status_code == 200
    data = response.json()
    assert data["category"] in ["refund", "complaint", "order", "urgent", "inquiry", "other"]
    assert data["urgency"] in ["low", "medium", "high"]

def test_classify_empty_validation():
    # 422 for pydantic validation min_length
    response = client.post("/classify", json={"text": ""})
    assert response.status_code in [400, 422]

def test_transcribe_invalid_format():
    response = client.post(
        "/transcribe",
        files={"file": ("test.txt", b"plain text", "text/plain")}
    )
    assert response.status_code == 400
    data = response.json()
    assert data["status"] == "error"
    assert data["error"] == "invalid_audio_format"
    assert "detail" in data

def test_transcribe_empty_file_error_json():
    response = client.post(
        "/transcribe",
        files={"file": ("empty.wav", b"", "audio/wav")}
    )
    assert response.status_code == 400
    data = response.json()
    assert data["status"] == "error"
    assert data["error"] == "empty_file"
    assert "detail" in data

def test_transcribe_internal_error_json(monkeypatch):
    from app.transcriber import AudioTranscriber
    def mock_transcribe(*args, **kwargs):
        raise RuntimeError("Simulated transcription failure for diagnostic test")

    monkeypatch.setattr(AudioTranscriber, "transcribe", mock_transcribe)
    wav_bytes = generate_test_wav_bytes(duration_sec=0.1)
    response = client.post(
        "/transcribe",
        files={"file": ("test_error.wav", wav_bytes, "audio/wav")}
    )
    assert response.status_code == 500
    data = response.json()
    assert data["status"] == "error"
    assert data["error"] == "transcription_failed"
    assert "Simulated transcription failure" in data["detail"]

def test_transcribe_audio_wav():
    wav_bytes = generate_test_wav_bytes(duration_sec=0.5)
    response = client.post(
        "/transcribe",
        files={"file": ("voice_sample.wav", wav_bytes, "audio/wav")}
    )
    assert response.status_code == 200
    data = response.json()
    assert "text" in data
    assert "language" in data

def test_transcribe_with_language_parameter():
    wav_bytes = generate_test_wav_bytes(duration_sec=0.5)
    
    # 1. Default language (ur)
    resp_def = client.post(
        "/transcribe",
        files={"file": ("sample_def.wav", wav_bytes, "audio/wav")}
    )
    assert resp_def.status_code == 200

    # 2. Forced English (en)
    resp_en = client.post(
        "/transcribe",
        files={"file": ("sample_en.wav", wav_bytes, "audio/wav")},
        data={"language": "en"}
    )
    assert resp_en.status_code == 200

    # 3. Auto detect (auto)
    resp_auto = client.post(
        "/transcribe",
        files={"file": ("sample_auto.wav", wav_bytes, "audio/wav")},
        data={"language": "auto"}
    )
    assert resp_auto.status_code == 200

def test_repeat_token_guard_detection():
    from app.transcriber import AudioTranscriber

    # 5+ consecutive repeating words should be detected as looping
    assert AudioTranscriber.detect_looping_output("bhai bhai bhai bhai bhai") is True
    assert AudioTranscriber.detect_looping_output("order order order order order") is True
    assert AudioTranscriber.detect_looping_output("tracking tracking tracking tracking tracking") is True

    # 5+ consecutive characters should be detected
    assert AudioTranscriber.detect_looping_output("एएएएए") is True
    assert AudioTranscriber.detect_looping_output("aaaaaa") is True

    # Normal text should NOT trigger looping
    assert AudioTranscriber.detect_looping_output("Assalam o alaikum mera order status batayein") is False
    assert AudioTranscriber.detect_looping_output("bhai mera parcel kab dispatch hoga") is False
    assert AudioTranscriber.detect_looping_output("") is False

    # clean_repeats should collapse looping words
    cleaned = AudioTranscriber.clean_repeats("parcel parcel parcel parcel parcel")
    assert cleaned == "parcel"

def test_shared_secret_auth(monkeypatch):
    """
    Verifies that when AI_API_KEY is set:
    1. /health is exempt from auth.
    2. Missing or wrong X-API-Key returns 401 Unauthorized.
    3. Matching X-API-Key succeeds.
    """
    secret = "fauri_test_secret_987"
    monkeypatch.setenv("AI_API_KEY", secret)

    # 1. /health is exempt (user requirement: except /health)
    health_resp = client.get("/health")
    assert health_resp.status_code == 200
    assert health_resp.json()["status"] == "ok"

    # 2. Missing X-API-Key on /classify -> 401
    unauth_resp = client.post("/classify", json={"text": "bhai mera parcel kab aega"})
    assert unauth_resp.status_code == 401
    assert unauth_resp.json()["error"] == "unauthorized"

    # 3. Invalid X-API-Key -> 401
    wrong_resp = client.post(
        "/classify",
        json={"text": "bhai mera parcel kab aega"},
        headers={"X-API-Key": "wrong_key"}
    )
    assert wrong_resp.status_code == 401

    # 4. Correct X-API-Key -> 200
    auth_resp = client.post(
        "/classify",
        json={"text": "bhai mera parcel kab aega"},
        headers={"X-API-Key": secret}
    )
    assert auth_resp.status_code == 200
    assert "category" in auth_resp.json()

def test_five_roman_urdu_negations():
    """
    Verifies that negation words (nahi, nhi, na, nahin) are preserved
    and correctly steer classification & urgency across 5 Roman Urdu samples.
    """
    from app.classifier import MessageClassifier
    clf = MessageClassifier.get_instance()

    # Negation Test 1: Non-delivery complaint (not an order)
    res1 = clf.classify("bhai order deliver nahi hua abhi tak 5 din ho gaye")
    assert res1["category"] == "complaint"
    assert res1["urgency"] in ["medium", "high"]

    # Negation Test 2: Rejecting replacement, requesting refund
    res2 = clf.classify("mujhe replacement nahi chahiye mere paise wapis karein")
    assert res2["category"] == "refund"
    assert res2["urgency"] in ["medium", "high"]

    # Negation Test 3: Disliking product, returning
    res3 = clf.classify("ye suit pasand nahi aya return krna hai")
    assert res3["category"] in ["refund", "complaint"]

    # Negation Test 4: Parcel not received, delivery rider issue
    res4 = clf.classify("parcel receive nhi hua rider ka number dein")
    assert res4["category"] == "complaint"
    assert res4["urgency"] in ["medium", "high"]

    # Negation Test 5: "cancel na karein" (Do NOT cancel order)
    res5 = clf.classify("mera order cancel na karein please dispatch krwa dein")
    assert res5["category"] == "order"


