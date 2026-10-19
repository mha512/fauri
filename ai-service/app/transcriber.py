"""
transcriber.py
Audio transcription using faster-whisper on CPU with int8 quantization.
Supports converting WhatsApp audio (ogg/opus, mp3, m4a, wav) via ffmpeg.
"""

import os
import tempfile
import subprocess
from typing import Dict, Any, Optional
from faster_whisper import WhisperModel

MODEL_SIZE = "small"
COMPUTE_TYPE = "int8"
DEVICE = "cpu"

class AudioTranscriber:
    _instance: Optional['AudioTranscriber'] = None

    def __init__(self):
        print(f"[Transcriber] Initializing faster-whisper model ('{MODEL_SIZE}', {DEVICE}, {COMPUTE_TYPE})...")
        self.model = WhisperModel(MODEL_SIZE, device=DEVICE, compute_type=COMPUTE_TYPE)
        print("[Transcriber] Whisper model loaded successfully.")

    @classmethod
    def get_instance(cls) -> 'AudioTranscriber':
        if cls._instance is None:
            cls._instance = cls()
        return cls._instance

    @staticmethod
    def convert_to_wav(input_path: str, output_path: str) -> None:
        """
        Uses ffmpeg to convert any audio format (ogg, opus, mp3, m4a, wav)
        to 16kHz mono WAV, optimal for Whisper models.
        """
        cmd = [
            "ffmpeg",
            "-y",
            "-i", input_path,
            "-ar", "16000",
            "-ac", "1",
            "-c:a", "pcm_s16le",
            output_path
        ]
        result = subprocess.run(cmd, stdout=subprocess.PIPE, stderr=subprocess.PIPE, text=True)
        if result.returncode != 0:
            raise RuntimeError(f"FFmpeg conversion failed: {result.stderr}")

    def transcribe(self, audio_bytes: bytes, original_filename: str = "audio.ogg") -> Dict[str, Any]:
        """
        Transcribes audio bytes into text with detected language.
        """
        ext = os.path.splitext(original_filename)[1].lower() or ".ogg"
        with tempfile.NamedTemporaryFile(suffix=ext, delete=False) as in_file:
            in_file.write(audio_bytes)
            in_path = in_file.name

        wav_path = in_path + ".converted.wav"
        try:
            # Convert with ffmpeg
            self.convert_to_wav(in_path, wav_path)

            # Transcribe with auto-detect (supports Urdu 'ur', English 'en', Punjabi, etc.)
            segments, info = self.model.transcribe(
                wav_path,
                beam_size=5,
                vad_filter=True,
                vad_parameters=dict(min_silence_duration_ms=500)
            )

            text_chunks = [segment.text.strip() for segment in segments]
            full_text = " ".join(text_chunks).strip()

            detected_language = info.language
            language_probability = float(info.language_probability)

            return {
                "text": full_text,
                "language": detected_language,
                "language_probability": round(language_probability, 4),
                "duration": round(float(info.duration), 2)
            }
        finally:
            if os.path.exists(in_path):
                try:
                    os.remove(in_path)
                except OSError:
                    pass
            if os.path.exists(wav_path):
                try:
                    os.remove(wav_path)
                except OSError:
                    pass
