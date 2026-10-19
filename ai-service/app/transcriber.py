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
import re
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

        logger.info("[Transcriber] Initializing faster-whisper model ('%s', %s, %s)...", self.model_size, self.device, self.compute_type)
        print(f"[Transcriber] Initializing faster-whisper model ('{self.model_size}', {self.device}, {self.compute_type})...")
        try:
            self.model = WhisperModel(self.model_size, device=self.device, compute_type=self.compute_type)
            logger.info("[Transcriber] Whisper model ('%s') loaded successfully.", self.model_size)
            print(f"[Transcriber] Whisper model ('{self.model_size}') loaded successfully.")
        except Exception as e:
            logger.warning("****************************************************************")
            logger.warning("[Transcriber] LOUD WARNING: Failed to load '%s' Whisper model: %s", self.model_size, e)
            print(f"[Transcriber] LOUD WARNING: Failed to load '{self.model_size}' model: {e}")
            if self.model_size != "tiny":
                logger.warning("[Transcriber] LOUD WARNING: Falling back to 'tiny' Whisper model!")
                logger.warning("****************************************************************")
                print("[Transcriber] LOUD WARNING: Falling back to 'tiny' Whisper model...")
                self.model_size = "tiny"
                self.model = WhisperModel("tiny", device=self.device, compute_type=self.compute_type)
                logger.info("[Transcriber] Whisper fallback model ('tiny') loaded successfully.")
                print("[Transcriber] Whisper fallback model ('tiny') loaded successfully.")
            else:
                logger.warning("****************************************************************")
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

    @staticmethod
    def detect_looping_output(text: str) -> bool:
        """
        Detects repeating/looping hallucinated tokens (same word/token or character repeated 5+ times).
        """
        if not text or len(text.strip()) == 0:
            return False
        clean = text.strip()

        # 1. Same word / token repeated 5+ times consecutively (e.g. 'token token token token token')
        if re.search(r'(\b[\w\u0600-\u06FF\u0900-\u097F]+\b)(?:\s+\1){4,}', clean, re.IGNORECASE | re.UNICODE):
            return True

        # 2. Same character repeated 5+ times consecutively (e.g. 'aaaaa', 'एएएएए')
        if re.search(r'([\w\u0600-\u06FF\u0900-\u097F])\1{4,}', clean, re.UNICODE):
            return True

        # 3. Repeated phrase (2-30 chars) repeated 5+ times consecutively
        if re.search(r'(.{2,30}?)(?:\s*\1){4,}', clean, re.UNICODE):
            return True

        return False

    @staticmethod
    def clean_repeats(text: str) -> str:
        """
        Collapses pathological 5+ repeated tokens down to a single instance.
        """
        if not text:
            return ""
        cleaned = re.sub(r'(\b[\w\u0600-\u06FF\u0900-\u097F]+\b)(?:\s+\1){4,}', r'\1', text, flags=re.IGNORECASE | re.UNICODE)
        cleaned = re.sub(r'([\w\u0600-\u06FF\u0900-\u097F])\1{4,}', r'\1', cleaned, flags=re.UNICODE)
        return cleaned.strip()

    def transcribe(self, audio_bytes: bytes, original_filename: str = "audio.ogg", language: Optional[str] = "ur") -> Dict[str, Any]:
        """
        Transcribes audio bytes into text with detected or forced language.
        Supports language parameter: 'ur' (default), 'en', 'auto' (None).
        Includes a repeat-token guard that detects looping output (5+ repetitions)
        and retries with forced language and temperature fallback.
        """
        if not audio_bytes:
            raise ValueError("Audio bytes cannot be empty.")

        # Resolve language param: 'auto' or None -> whisper auto-detection (language=None)
        whisper_lang: Optional[str] = None
        if language and language.lower() not in ("auto", "none", ""):
            whisper_lang = language.lower()
        else:
            whisper_lang = None

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

            # Initial transcription run
            segments, info = self.model.transcribe(
                wav_path,
                language=whisper_lang,
                beam_size=5,
                vad_filter=True,
                vad_parameters=dict(min_silence_duration_ms=500)
            )

            text_chunks = [segment.text.strip() for segment in segments]
            full_text = " ".join(text_chunks).strip()

            detected_language = info.language or (whisper_lang or "ur")
            language_probability = float(getattr(info, "language_probability", 1.0) or 1.0)
            duration = float(getattr(info, "duration", 0.0) or 0.0)

            # Repeat-token guard: detect looping output (5+ repeats)
            if self.detect_looping_output(full_text):
                logger.warning(
                    "[Transcriber] Looping token/text detected ('%s...'). Retrying with forced language ('ur') and temperature fallback (0.2).",
                    full_text[:50]
                )
                print(f"[Transcriber] Looping output detected ('{full_text[:50]}...'). Retrying with forced language ('ur') and temperature fallback...")

                retry_segments, retry_info = self.model.transcribe(
                    wav_path,
                    language="ur",
                    temperature=0.2,
                    condition_on_previous_text=False,
                    beam_size=5,
                    vad_filter=True,
                    vad_parameters=dict(min_silence_duration_ms=500)
                )
                retry_chunks = [segment.text.strip() for segment in retry_segments]
                retry_text = " ".join(retry_chunks).strip()

                if not self.detect_looping_output(retry_text) and retry_text:
                    full_text = retry_text
                    detected_language = retry_info.language or "ur"
                    language_probability = float(getattr(retry_info, "language_probability", 1.0) or 1.0)
                    logger.info("[Transcriber] Looping resolved via retry: '%s'", full_text)
                    print(f"[Transcriber] Looping resolved via retry: '{full_text}'")
                else:
                    # Clean the looping repetitive tokens
                    full_text = self.clean_repeats(retry_text or full_text)

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
