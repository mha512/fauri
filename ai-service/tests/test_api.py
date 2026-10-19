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
