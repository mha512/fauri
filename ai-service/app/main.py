"""
main.py
FastAPI application for Fauri message classification and audio transcription.
"""

import os
import time
import logging
from typing import Dict, Any, Optional
from fastapi import FastAPI, HTTPException, UploadFile, File, Form, status
from fastapi.responses import JSONResponse
from fastapi.middleware.cors import CORSMiddleware
from pydantic import BaseModel, Field

from app.classifier import MessageClassifier
from app.transcriber import AudioTranscriber

logging.basicConfig(
    level=logging.INFO,
    format="%(asctime)s [%(levelname)s] %(name)s: %(message)s"
)
logger = logging.getLogger("fauri.api")

app = FastAPI(
    title="Fauri AI Service",
    description="Urdu/Roman Urdu/English WhatsApp Customer Message Triage Engine",
    version="1.0.0"
)

# CORS middleware for Laravel or frontend integration
app.add_middleware(
    CORSMiddleware,
    allow_origins=["*"],
    allow_credentials=True,
    allow_methods=["*"],
    allow_headers=["*"],
)

# Request & Response schemas
class ClassifyRequest(BaseModel):
    text: str = Field(..., description="Message text in Roman Urdu, Urdu Nastaliq, or English", min_length=1)

class ClassifyResponse(BaseModel):
    category: str
    urgency: str
    summary: str
    confidence: float

class TranscribeResponse(BaseModel):
    text: str
    language: str
    duration: Optional[float] = None
    language_probability: Optional[float] = None

class ErrorResponse(BaseModel):
    error: str = Field(..., description="Machine-readable error code")
    message: str = Field(..., description="Human-readable error message")
    detail: Optional[str] = Field(None, description="Detailed diagnostic or root cause")
    status: str = Field("error", description="Error status indicator")

# Model handles (lazily loaded or prewarmed)
_classifier: Optional[MessageClassifier] = None
_transcriber: Optional[AudioTranscriber] = None

def get_classifier() -> MessageClassifier:
    global _classifier
    if _classifier is None:
        _classifier = MessageClassifier.get_instance()
    return _classifier

def get_transcriber() -> AudioTranscriber:
    global _transcriber
    if _transcriber is None:
        _transcriber = AudioTranscriber.get_instance()
    return _transcriber

@app.get("/")
def root():
    return {
        "service": "Fauri AI Service",
        "description": "WhatsApp customer message classification & transcription for Pakistani SMBs",
        "version": "1.0.0",
        "endpoints": ["/health", "/classify", "/transcribe"]
    }

@app.get("/health")
def health_check():
    """
    Instant health check endpoint returning service status and model availability.
    """
    global _classifier, _transcriber
    classifier_ready = _classifier is not None and _classifier.is_ready()
    transcriber_ready = _transcriber is not None and _transcriber.is_ready()
    stt_model_name = (
        f"Systran/faster-whisper-{_transcriber.model_size}"
        if (_transcriber and _transcriber.is_ready())
        else f"Systran/faster-whisper-{os.getenv('WHISPER_MODEL', 'small')}"
    )

    return {
        "status": "ok",
        "service": "fauri-ai",
        "timestamp": time.time(),
        "models": {
            "classifier_ready": classifier_ready,
            "transcriber_ready": transcriber_ready,
            "stt_model": stt_model_name,
            "embeddings_model": "sentence-transformers/paraphrase-multilingual-MiniLM-L12-v2"
        }
    }

@app.post("/classify", response_model=ClassifyResponse)
def classify_message(payload: ClassifyRequest):
    """
    Classifies customer message into order, complaint, refund, inquiry, urgent, or other,
    detects urgency (low, medium, high), and generates a crisp summary.
    """
    text = payload.text.strip()
    if not text:
        raise HTTPException(status_code=400, detail="Text field cannot be empty.")

    try:
        clf = get_classifier()
        result = clf.classify(text)
        return ClassifyResponse(**result)
    except Exception as e:
        logger.exception("Error in classification: %s", e)
        raise HTTPException(status_code=500, detail=f"Classification error: {str(e)}")

@app.post(
    "/transcribe",
    response_model=TranscribeResponse,
    responses={
        400: {"model": ErrorResponse, "description": "Client error (invalid format, empty file)"},
        500: {"model": ErrorResponse, "description": "Server error during audio conversion or model transcription"}
    }
)
async def transcribe_audio(file: UploadFile = File(...)):
    """
    Accepts WhatsApp audio formats (ogg/opus, mp3, m4a, wav, aac), converts via ffmpeg,
    and transcribes using faster-whisper with language auto-detection.
    Returns clear, structured JSON on any error instead of a bare 500.
    """
    allowed_extensions = {".ogg", ".opus", ".mp3", ".m4a", ".wav", ".aac"}
    filename = file.filename or "audio.ogg"
    ext = os.path.splitext(filename)[1].lower()

    if ext not in allowed_extensions and not filename.endswith(tuple(allowed_extensions)):
        # Check MIME type if extension is not in standard list
        if not file.content_type or not file.content_type.startswith("audio/"):
            return JSONResponse(
                status_code=status.HTTP_400_BAD_REQUEST,
                content={
                    "error": "invalid_audio_format",
                    "message": f"Unsupported file format '{ext}'.",
                    "detail": f"Supported audio formats: {', '.join(sorted(allowed_extensions))}",
                    "status": "error"
                }
            )

    try:
        content = await file.read()
        if not content or len(content) == 0:
            return JSONResponse(
                status_code=status.HTTP_400_BAD_REQUEST,
                content={
                    "error": "empty_file",
                    "message": "Uploaded audio file is empty.",
                    "detail": "Received 0 bytes in audio upload payload.",
                    "status": "error"
                }
            )

        transcriber = get_transcriber()
        result = transcriber.transcribe(content, original_filename=filename)
        return TranscribeResponse(**result)

    except HTTPException as he:
        return JSONResponse(
            status_code=he.status_code,
            content={
                "error": "http_error",
                "message": str(he.detail),
                "detail": str(he.detail),
                "status": "error"
            }
        )
    except Exception as e:
        logger.exception("Error during audio transcription: %s", e)
        return JSONResponse(
            status_code=status.HTTP_500_INTERNAL_SERVER_ERROR,
            content={
                "error": "transcription_failed",
                "message": "An error occurred during audio transcription.",
                "detail": str(e),
                "status": "error"
            }
        )
