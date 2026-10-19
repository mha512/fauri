# Fauri AI Service (فوری)

High-performance, CPU-friendly microservice for classifying and transcribing multilingual Pakistani customer messages (Roman Urdu, Urdu script, and English).

---

## 1. Overview & Architecture

- **Endpoints:**
  - `POST /classify`: Classifies incoming customer messages into 6 business categories (`order`, `complaint`, `refund`, `inquiry`, `urgent`, `other`), detects urgency (`low`, `medium`, `high`) via hybrid heuristics, and generates a structured summary.
  - `POST /transcribe`: Transcribes WhatsApp voice notes (`ogg/opus`, `mp3`, `m4a`, `wav`) via `ffmpeg` conversion and `faster-whisper` (`Systran/faster-whisper-small` with `int8` CPU quantization).
  - `GET /health`: Instant liveness & readiness check detailing loaded models.
- **Hardware Constraints:** 100% CPU-only execution, runs within free-tier specs (<16GB RAM, ~1.2GB working footprint), zero paid API dependencies, zero external LLM calls.

---

## 2. Model Training & Accuracy Benchmark

### Training Pipeline
- **Dataset:** 600 balanced synthetic samples (`data/messages.csv`) covering authentic Pakistani commerce contexts (Daraz/Instagram apparel & cosmetics, medical clinics, tuition academies, real estate).
- **Linguistic Coverage:** Heavy code-switching, Roman Urdu slang, spelling variants (`kya / kia / kiya`, `nahi / nhi / nai`, `bhejo / bhijwa dein`, `pesay / paise`, etc.), and Urdu Nastaliq text.
- **Embedding Model:** `sentence-transformers/paraphrase-multilingual-MiniLM-L12-v2` (384-dimensional dense vectors).
- **Classifier:** scikit-learn `LogisticRegression(C=2.0, class_weight='balanced')`.

### Benchmark Results (80/20 Stratified Split)

```text
============================================================
TRAIN ACCURACY: 99.58%
TEST ACCURACY:  84.17%
============================================================

CLASSIFICATION REPORT (TEST SET - 120 SAMPLES):

              precision    recall  f1-score   support

   complaint     0.8667    0.6500    0.7429        20
     inquiry     0.8571    0.9000    0.8780        20
       order     0.8261    0.9500    0.8837        20
       other     0.7917    0.9500    0.8636        20
      refund     0.8000    0.8000    0.8000        20
      urgent     0.9412    0.8000    0.8649        20

    accuracy                         0.8417       120
   macro avg     0.8471    0.8417    0.8389       120
weighted avg     0.8471    0.8417    0.8389       120
```

### Honest Assessment of Synthetic Data
> [!IMPORTANT]
> **Synthetic-data accuracy (84.17%) is inherently optimistic.**
> In production environments across Pakistani SMBs, live customer voice notes and WhatsApp chats exhibit:
> 1. Non-standard phonetic Roman Urdu spellings (e.g. `mjhe smn chahye btao kb tk bhejoge`).
> 2. Punctuation-free run-on sentences mixing complaints with new inquiries.
> 3. Regional dialect words (Punjabi/Sindhi/Pashto loanwords like `chheti`, `theek-thaak`).
> 
> **Workaround & Next Steps:** Fauri logs classified messages to SQLite/MySQL. Business owners can correct misclassifications with 1 click in the dashboard, accumulating authentic ground-truth data for weekly scheduled retraining.

---

## 3. Running Locally

```bash
# Install dependencies
pip install -r requirements.txt

# Run dataset generation & training (creates models/classifier.pkl)
python data/generate_dataset.py
python train.py

# Run FastAPI server
uvicorn app.main:app --host 0.0.0.0 --port 8001 --reload
```

---

## 4. Running Tests

```bash
pytest -v
```

---

## 5. Hugging Face Spaces Deployment

The included `Dockerfile` conforms to Hugging Face Spaces Docker SDK guidelines (runs as non-root user UID 1000 on port 7860 with pre-cached models).
