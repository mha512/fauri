"""
transcriber.py
Audio transcription using faster-whisper on CPU with int8 quantization.
Supports converting WhatsApp audio (ogg/opus, mp3, m4a, wav) via ffmpeg.
Includes adaptive model selection and resilient fallback to handle
resource-constrained environments.
"""

import os
import shutil
import tempfile
import subprocess
import logging
from typing import Dict, Any, Optional
from faster_whisper import WhisperModel

logger = logging.getLogger("fauri.transcriber")

DEFAULT_COMPUTE_TYPE = "int8"
DEFAULT_DEVICE = "cpu"

def get_default_model_size() -> str:
    """
    Selects model size based on environment variable WHISPER_MODEL,
    falling back to 'tiny' if disk space is constrained (<1.5GB free),
    or 'small' if adequate space is available.
    """
    env_model = os.getenv("WHISPER_MODEL")
    if env_model:
        return env_model

    try:
        # Check available disk space on the home/drive partition
        path_to_check = os.path.expanduser("~")
        if os.name == "nt":
            drive = os.path.splitdrive(os.path.abspath(path_to_check))[0] or "C:"
            total, used, free = shutil.disk_usage(drive + "\\")
        else:
            total, used, free = shutil.disk_usage(path_to_check)

        # If less than 1.5 GB free, default to tiny to avoid out-of-disk IO/malloc errors
        if free < 1.5 * 1024 * 1024 * 1024:
            return "tiny"
    except Exception as e:
        logger.warning("Could not determine disk usage (%s), defaulting to 'tiny'", e)
        return "tiny"

    return "small"


class AudioTranscriber:
    _instance: Optional['AudioTranscriber'] = None

    def __init__(self, model_size: Optional[str] = None):
        target_model = model_size or get_default_model_size()
        self.device = os.getenv("WHISPER_DEVICE", DEFAULT_DEVICE)
        self.compute_type = os.getenv("WHISPER_COMPUTE_TYPE", DEFAULT_COMPUTE_TYPE)
        self.model_size = target_model
        self.model: Optional[WhisperModel] = None

        print(f"[Transcriber] Initializing faster-whisper model ('{self.model_size}', {self.device}, {self.compute_type})...")
        try:
            self.model = WhisperModel(self.model_size, device=self.device, compute_type=self.compute_type)
            print(f"[Transcriber] Whisper model ('{self.model_size}') loaded successfully.")
        except Exception as e:
            print(f"[Transcriber] Warning: Failed to load '{self.model_size}' model: {e}")
            if self.model_size != "tiny":
                print("[Transcriber] Falling back to 'tiny' Whisper model...")
                self.model_size = "tiny"
                self.model = WhisperModel("tiny", device=self.device, compute_type=self.compute_type)
                print("[Transcriber] Whisper fallback model ('tiny') loaded successfully.")
            else:
                raise e

    def is_ready(self) -> bool:
        return self.model is not None

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
        try:
            result = subprocess.run(cmd, stdout=subprocess.PIPE, stderr=subprocess.PIPE, text=True, timeout=30)
            if result.returncode != 0:
                raise RuntimeError(f"FFmpeg conversion failed: {result.stderr.strip() or 'Unknown error'}")
        except FileNotFoundError:
            if input_path.lower().endswith(".wav"):
                shutil.copyfile(input_path, output_path)
            else:
                raise RuntimeError("FFmpeg is not installed or not found in system PATH.")
        except subprocess.TimeoutExpired:
            raise RuntimeError("FFmpeg conversion timed out after 30 seconds.")

    def transcribe(self, audio_bytes: bytes, original_filename: str = "audio.ogg") -> Dict[str, Any]:
        """
        Transcribes audio bytes into text with detected language.
        """
        if not audio_bytes:
            raise ValueError("Audio bytes cannot be empty.")

        ext = os.path.splitext(original_filename)[1].lower() or ".ogg"
        with tempfile.NamedTemporaryFile(suffix=ext, delete=False) as in_file:
            in_file.write(audio_bytes)
            in_path = in_file.name

        wav_path = in_path + ".converted.wav"
        try:
            # Convert with ffmpeg
            self.convert_to_wav(in_path, wav_path)

            if not os.path.exists(wav_path) or os.path.getsize(wav_path) == 0:
                raise RuntimeError("Audio conversion produced an empty or missing WAV file.")

            # Transcribe with auto-detect (supports Urdu 'ur', English 'en', Punjabi, etc.)
            segments, info = self.model.transcribe(
                wav_path,
                beam_size=5,
                vad_filter=True,
                vad_parameters=dict(min_silence_duration_ms=500)
            )

            text_chunks = [segment.text.strip() for segment in segments]
            full_text = " ".join(text_chunks).strip()

            detected_language = info.language or "ur"
            language_probability = float(getattr(info, "language_probability", 1.0) or 1.0)
            duration = float(getattr(info, "duration", 0.0) or 0.0)

            return {
                "text": full_text,
                "language": detected_language,
                "language_probability": round(language_probability, 4),
                "duration": round(duration, 2)
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
