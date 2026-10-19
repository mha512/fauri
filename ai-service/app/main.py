"""
main.py
FastAPI application for Fauri message classification and audio transcription.
"""

import os
import time
from typing import Dict, Any, Optional
from fastapi import FastAPI, HTTPException, UploadFile, File, Form
from fastapi.middleware.cors import CORSMiddleware
from pydantic import BaseModel, Field

from app.classifier import MessageClassifier
from app.transcriber import AudioTranscriber

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

# Request schemas
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
    Health check endpoint returning service status and model availability.
    """
    classifier_ready = False
    try:
        clf = get_classifier()
        classifier_ready = clf.is_ready()
    except Exception as e:
        classifier_ready = False

    return {
        "status": "ok",
        "service": "fauri-ai",
        "timestamp": time.time(),
        "models": {
            "classifier_ready": classifier_ready,
            "stt_model": "Systran/faster-whisper-small",
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
        raise HTTPException(status_code=500, detail=f"Classification error: {str(e)}")

@app.post("/transcribe", response_model=TranscribeResponse)
async def transcribe_audio(file: UploadFile = File(...)):
    """
    Accepts WhatsApp audio formats (ogg/opus, mp3, m4a, wav), converts via ffmpeg,
    and transcribes using faster-whisper with language auto-detection.
    """
    allowed_extensions = {".ogg", ".opus", ".mp3", ".m4a", ".wav", ".aac"}
    filename = file.filename or "audio.ogg"
    ext = os.path.splitext(filename)[1].lower()

    if ext not in allowed_extensions and not filename.endswith(tuple(allowed_extensions)):
        # Still attempt if mime type is audio
        if not file.content_type or not file.content_type.startswith("audio/"):
            raise HTTPException(
                status_code=400,
                detail=f"Unsupported file format '{ext}'. Supported formats: {', '.join(allowed_extensions)}"
            )

    try:
        content = await file.read()
        if not content:
            raise HTTPException(status_code=400, detail="Uploaded audio file is empty.")

        transcriber = get_transcriber()
        result = transcriber.transcribe(content, original_filename=filename)
        return TranscribeResponse(**result)
    except Exception as e:
        raise HTTPException(status_code=500, detail=f"Transcription error: {str(e)}")
