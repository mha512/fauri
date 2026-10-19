# Fauri (فوری) — Intelligent WhatsApp Message Triage for Pakistani Commerce

<div align="center">
  <img src="web/public/brand/logo-light.svg" alt="Fauri Logo" width="340" />
  <p><strong>"فوری جواب، مطمئن گاہک"</strong> — Instant Response, Satisfied Customer.</p>
  <p>An autonomous, multi-tenant customer message triage platform built for Pakistani online sellers, clinics, academies, and service businesses dealing with high volumes of Roman Urdu, Urdu Nastaliq, and English voice notes.</p>

  [![Laravel 11](https://img.shields.io/badge/Laravel-11-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)](https://laravel.com)
  [![FastAPI](https://img.shields.io/badge/FastAPI-0.110-009688?style=for-the-badge&logo=fastapi&logoColor=white)](https://fastapi.tiangolo.com)
  [![Whisper STT](https://img.shields.io/badge/faster--whisper-small_int8-blue?style=for-the-badge)](https://github.com/SYSTRAN/faster-whisper)
  [![HuggingFace](https://img.shields.io/badge/MiniLM-L12--v2-yellow?style=for-the-badge&logo=huggingface)](https://huggingface.co/sentence-transformers/paraphrase-multilingual-MiniLM-L12-v2)
  [![License](https://img.shields.io/badge/License-MIT-emerald?style=for-the-badge)](LICENSE)
</div>

---

## 1. System Architecture

```mermaid
flowchart TD
    subgraph Ingestion["1. Customer Communication Channels"]
        WA["WhatsApp Voice Notes\n(.opus / .ogg / .m4a / .wav)"]
        TXT["WhatsApp Chat Export\n(.txt files)"]
        PST["Direct Web Paste\n(Roman Urdu / Urdu / English)"]
    end

    subgraph Web["2. Laravel 11 Core Service (Port 8000)"]
        Breeze["Breeze Multi-Business Auth\n(Data Isolation)"]
        Parser["WhatsApp Export Parser\n(Auto Customer Matching)"]
        DB[("Database Storage\nSQLite / MySQL")]
        Queue["Database Background Queue\n(3 Retries + Exponential Backoff)"]
        UI["Livewire 3 + Tailwind CSS\n(Urdu Nastaliq RTL + Dark Mode)"]
    end

    subgraph AI["3. Python 3.11 AI Microservice (Port 8001 / HF Spaces)"]
        FFmpeg["FFmpeg Audio Converter\n(16kHz Mono WAV)"]
        Whisper["faster-whisper CPU int8\n(Urdu & English STT)"]
        Embedder["Sentence-Transformers\n(MiniLM-L12-v2 Multilingual)"]
        Classifier["scikit-learn LogisticRegression\n(6 Categories + Ground Truth Corrections)"]
        Rules["Heuristic Urgency & Summary Rules\n(Low / Medium / High Priority)"]
    end

    WA -->|Audio Upload| UI
    TXT -->|Batch Import| Parser
    PST -->|Manual Ingest| UI

    Parser --> DB
    UI --> DB
    DB --> Queue

    Queue -->|Audio Stream| FFmpeg --> Whisper --> Embedder
    Queue -->|Text Payload| Embedder --> Classifier --> Rules

    Rules -->|Category, Urgency, Summary| Queue
    Queue -->|Update Status & Confidence| DB
    DB -->|Real-time Reactive Updates| UI
```

---

## 2. Product Features & Design Highlights

- **Bilingual & RTL Aware:** Native support for Urdu script rendered with Google Fonts' `Noto Nastaliq Urdu` with automated right-to-left baseline detection (`dir="rtl"`).
- **Voice Note Transcription:** Seamlessly converts WhatsApp voice notes (`ogg`, `opus`, `mp3`, `m4a`, `wav`) via FFmpeg and transcribes with `faster-whisper` (`int8` quantized for high CPU throughput).
- **Urgency Scoring Engine:** Detects high-priority wedding deadlines, medical emergencies, credit card fraud disputes, and rider delivery delays across Roman Urdu (`"foran dispatch krwaen"`, `"khuda k waste"`), Urdu script (`"فوری ایمرجنسی"`), and English (`"immediate cancellation"`).
- **Active Learning Human Correction:** Business owners can correct any misclassified message with 1 click in the detail drawer, accumulating authentic ground truth for automated weekly retraining.
- **Zero API Costs & Free-Tier Friendly:** 100% CPU execution requiring < 1.5GB RAM footprint, zero paid LLM tokens, zero recurring cloud subscriptions.

---

## 3. UI/UX Walkthrough & Screenshots

```text
┌──────────────────────────────────────────────────────────────────────────────────────────────────┐
│  [ Fauri فوری ]   Dashboard   Customers                       [ Gulberg Apparel ]  [ EN / اردو ] │
├──────────────────────────────────────────────────────────────────────────────────────────────────┤
│                                                                                                  │
│   ┌─────────────────┐   ┌─────────────────┐   ┌─────────────────┐   ┌─────────────────┐          │
│   │  Today's Feed   │   │ Urgent Attention│   │ Pending Triage  │   │    Resolved     │          │
│   │       20        │   │    3 CRITICAL   │   │       11        │   │        9        │          │
│   └─────────────────┘   └─────────────────┘   └─────────────────┘   └─────────────────┘          │
│                                                                                                  │
│  🚨 TOP CRITICAL FEED:                                                                           │
│  • Fatima Zahra: "URGENT: kal meri shadi ka event hai bridal dress abhi tak receive nahi hua!"   │
│  • Dr. Asim Farooq: "فوری ایمرجنسی ہے مریض کو دوائی کا شدید ری ایکشن ہوا ہے..."                 │
│                                                                                                  │
│  📊 CATEGORY SPECTRUM:                                                                           │
│  [■■■ Orders 35%] [■■ Complaints 20%] [■■ Refunds 15%] [■ Urgent 15%] [■■ Inquiries 15%]        │
│                                                                                                  │
│  📥 TRIAGE INBOX:                                                                                │
│  [ Search Roman Urdu or English... ] [ Category: All ▾ ] [ Urgency: All ▾ ] [ Status: All ▾ ]    │
│  ──────────────────────────────────────────────────────────────────────────────────────────────  │
│  [✓] Hamza Sheikh  • DHA Lahore   "AOA bhai black linen suit medium size COD pe..."  [Pending ▾] │
│  [✓] Sana Mir      • Voice Note   "bhai royal blue mangwaya tha brown bhej dia..."   [In Progress│
│  [✓] Bilal Ahmed   • Leopard TCS  "return deliver ho gya 4200 JazzCash pe bhejo"     [Resolved]  │
└──────────────────────────────────────────────────────────────────────────────────────────────────┘
```

---

## 4. Quickstart: One-Command Local Setup

You can run both the Laravel web application and the Python AI service using Docker Compose:

```bash
docker compose up --build
```

- **Web Application & Dashboard:** [http://localhost:8000](http://localhost:8000)
  - Demo Account: `demo@fauri.pk`
  - Demo Password: `password`
- **AI Microservice & Health Probe:** [http://localhost:8001/health](http://localhost:8001/health)

---

## 5. Manual Local Development

### 1. AI Service Setup
```bash
cd ai-service

# Install dependencies
pip install -r requirements.txt

# Generate synthetic dataset and train classifier
python data/generate_dataset.py
python train.py

# Run tests
pytest tests/ -v

# Start FastAPI server
uvicorn app.main:app --host 127.0.0.1 --port 8001
```

### 2. Laravel Web Setup
```bash
cd web

# Install PHP dependencies
composer install

# Install Node dependencies and compile assets
npm install
npm run build

# Configure environment
cp .env.example .env
php artisan key:generate

# Run migrations and seed Pakistani SMB demo data
php artisan migrate:fresh --seed

# Run Laravel test suite
php artisan test

# Start development server
php artisan serve --port=8000
```

---

## 6. Model Training & Accuracy Benchmark

- **Dataset:** 600 balanced synthetic messages in Roman Urdu, Urdu script, and English (`ai-service/data/messages.csv`).
- **Embedding Model:** `sentence-transformers/paraphrase-multilingual-MiniLM-L12-v2` (384-dimensional).
- **Classifier:** scikit-learn `LogisticRegression(C=2.0)`.

```text
============================================================
TRAIN ACCURACY: 99.58%
TEST ACCURACY:  84.17% (Stratified 80/20 Test Split)
============================================================

               precision    recall  f1-score   support
   complaint      0.8667    0.6500    0.7429        20
     inquiry      0.8571    0.9000    0.8780        20
       order      0.8261    0.9500    0.8837        20
       other      0.7917    0.9500    0.8636        20
      refund      0.8000    0.8000    0.8000        20
      urgent      0.9412    0.8000    0.8649        20
    accuracy                          0.8417       120
```

> **Note on Real-World Accuracy:** Synthetic-data accuracy is optimistic. Real WhatsApp messages feature non-standard phonetic spellings and noisy code-switching. Fauri includes an inline human correction loop so merchants can actively build authentic training data directly from their daily operations.

---

## 7. Free Production Deployment

See [DEPLOY.md](file:///c:/Users/Abdullah/Desktop/PSL%20ANALYZER/DEPLOY.md) for detailed step-by-step instructions:
- **AI Service:** Hosted on Hugging Face Spaces (Docker SDK, port 7860, free 16GB CPU).
- **Web App:** Hosted on Render.com (Docker web service, free tier, database queues).
- **Database:** Free cloud MySQL (Aiven / TiDB Cloud) or persistent SQLite.

---

## 8. Brand Identity

See [BRAND.md](file:///c:/Users/Abdullah/Desktop/PSL%20ANALYZER/BRAND.md) for the complete design system, typography rationale, and brand naming etymology.
