"""
classifier.py
Sentence-Transformers multilingual embedder + LogisticRegression classifier.
"""

import os
import joblib
import numpy as np
from typing import Dict, Any, Optional
from sentence_transformers import SentenceTransformer
from app.rules import detect_urgency, generate_summary

BASE_DIR = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
MODEL_FILE = os.path.join(BASE_DIR, "models", "classifier.pkl")
EMBEDDING_MODEL_NAME = "sentence-transformers/paraphrase-multilingual-MiniLM-L12-v2"

class MessageClassifier:
    _instance: Optional['MessageClassifier'] = None

    def __init__(self):
        print(f"[Classifier] Loading SentenceTransformer: {EMBEDDING_MODEL_NAME} on CPU...")
        self.embedder = SentenceTransformer(EMBEDDING_MODEL_NAME, device="cpu")
        self.model_payload = None
        self.clf = None
        self.classes = []

        if os.path.exists(MODEL_FILE):
            print(f"[Classifier] Loading trained model from {MODEL_FILE}...")
            self.model_payload = joblib.load(MODEL_FILE)
            self.clf = self.model_payload.get("classifier")
            self.classes = self.model_payload.get("classes", [])
            print(f"[Classifier] Loaded classifier with classes: {self.classes}")
        else:
            print(f"[Classifier] Warning: {MODEL_FILE} not found. Will need training before inference.")

    @classmethod
    def get_instance(cls) -> 'MessageClassifier':
        if cls._instance is None:
            cls._instance = cls()
        return cls._instance

    def is_ready(self) -> bool:
        return self.clf is not None and self.embedder is not None

    def reload_model(self):
        if os.path.exists(MODEL_FILE):
            self.model_payload = joblib.load(MODEL_FILE)
            self.clf = self.model_payload.get("classifier")
            self.classes = self.model_payload.get("classes", [])
            print(f"[Classifier] Model reloaded successfully.")

    def classify(self, text: str) -> Dict[str, Any]:
        if not text or not text.strip():
            return {
                "category": "other",
                "urgency": "low",
                "summary": "Empty message",
                "confidence": 0.0
            }

        if not self.is_ready():
            # If not yet trained, reload or provide fallback
            self.reload_model()

        if not self.is_ready():
            # Basic fallback if model missing
            urgency = detect_urgency(text, "other")
            summary = generate_summary(text, "other", urgency)
            return {
                "category": "other",
                "urgency": urgency,
                "summary": summary,
                "confidence": 0.5
            }

        # Embed text
        emb = self.embedder.encode([text], device="cpu")
        probs = self.clf.predict_proba(emb)[0]
        max_idx = int(np.argmax(probs))
        category = self.clf.classes_[max_idx]
        confidence = float(probs[max_idx])

        # Urgency & Summary heuristics
        urgency = detect_urgency(text, category)
        summary = generate_summary(text, category, urgency)

        return {
            "category": category,
            "urgency": urgency,
            "summary": summary,
            "confidence": round(confidence, 4)
        }
