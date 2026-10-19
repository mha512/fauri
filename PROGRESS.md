# Fauri (فوری) - Project Progress Tracker

An intelligent customer-message triage platform for Pakistani SMBs (WhatsApp text and voice notes in Roman Urdu, Urdu, and English).

---

## Overall Status: Phase 1 Completed, Starting Phase 2

| Phase | Description | Status | Proof / Artifacts |
|---|---|---|---|
| **Phase 1** | **AI Service (/ai-service)**: FastAPI, Hugging Face multilingual embeddings + LogisticRegression classifier, faster-whisper STT, 600 synthetic messages dataset, tests, Dockerfile | **COMPLETED** | `ai-service/`, 11/11 tests passed, 84.17% test accuracy |
| **Phase 2** | **Laravel Core (/web)**: Laravel 11, Breeze, multi-business isolation, SQLite/MySQL migrations, Models, WhatsApp export parser, Voice note uploads, Demo seeder | **IN PROGRESS** | `web/` |
| **Phase 3** | **Integration**: Queued background jobs (`ClassifyMessageJob`, `TranscribeAudioJob`), retry with backoff, resilient fallbacks | **PENDING** | `web/app/Jobs/` |
| **Phase 4** | **UI/UX**: Modern classic theme, responsive landing page, dashboard with urgency feed, category chart, dark/light mode, Urdu Nastaliq RTL, audio player, toasts | **PENDING** | `web/resources/views/` |
| **Phase 5** | **Deployment Prep**: HF Space Dockerfile, Render Dockerfile + render.yaml, DEPLOY.md, docker-compose.yml, README.md | **PENDING** | Root config files |

---

## Detailed Log

### Phase 1: AI Service (Completed)
- [x] Initialized Git repository
- [x] Verified system environment: PHP 8.5, Composer 2.10, Python 3.11, Node 24, Git, FFmpeg 9.0
- [x] Enabled `pdo_sqlite`, `sqlite3`, `zip`, and `gd` extensions in PHP
- [x] Formulated brand identity in `BRAND.md` (Name: **Fauri / فوری**, meaning, 2 rejected alternatives, modern classic palette)
- [x] Created clean SVG brand assets in `/web/public/brand/` (`logomark.svg`, `logo-light.svg`, `logo-dark.svg`, `favicon.svg`)
- [x] Generated 600 balanced synthetic messages in `ai-service/data/messages.csv` (100 per category: order, complaint, refund, inquiry, urgent, other) with authentic Roman Urdu, Urdu Nastaliq, and English, plus phonetic spelling variations.
- [x] Implemented heuristic rules in `ai-service/app/rules.py` for urgency scoring (low/medium/high) and rule-based template summaries.
- [x] Built training pipeline `train.py` using `sentence-transformers/paraphrase-multilingual-MiniLM-L12-v2` + scikit-learn `LogisticRegression`.
- [x] Saved trained model to `ai-service/models/classifier.pkl` (Train accuracy: 99.58%, Test accuracy: 84.17%).
- [x] Implemented faster-whisper speech-to-text with `Systran/faster-whisper-small` (int8, CPU) and ffmpeg conversion in `ai-service/app/transcriber.py`.
- [x] Created FastAPI application `ai-service/app/main.py` with `/health`, `/classify`, `/transcribe` endpoints and CORS.
- [x] Wrote comprehensive pytest suite `ai-service/tests/` (11 unit and integration tests). All 11 passed.
- [x] Created Hugging Face Spaces `Dockerfile` (port 7860, non-root user, ffmpeg, model pre-caching).
- [x] Documented model performance and synthetic data disclaimer in `ai-service/README.md`.
- [x] Committed Phase 1 to Git.

### Phase 2: Laravel Core (In Progress)
- [ ] Initialize Laravel 11 project in `/web`
- [ ] Install Laravel Breeze and Livewire 3
- [ ] Set up SQLite database and multi-business migrations:
  - `businesses` (name, slug, owner/user_id, settings)
  - `customers` (business_id, name, phone, email, notes)
  - `messages` (business_id, customer_id, body, source [paste, whatsapp_export, voice], category, urgency, summary, confidence, status [pending, in_progress, resolved], audio_path, timestamps)
- [ ] Build models with relationships and scopes
- [ ] Implement WhatsApp `.txt` chat export parser (automatic customer matching/creation, timestamps, sender parsing)
- [ ] Implement Manual message paste and Voice note upload handlers
- [ ] Create comprehensive database seeder with realistic Pakistani SMB demo data
- [ ] Run migrations, seed, test, and commit Phase 2

---

*Updated automatically after each milestone.*
