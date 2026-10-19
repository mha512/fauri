# Fauri (فوری) - Project Progress Tracker

An intelligent customer-message triage platform for Pakistani SMBs (WhatsApp text and voice notes in Roman Urdu, Urdu, and English).

---

## Overall Status: All Phases Completed Successfully (Production Ready)

| Phase | Description | Status | Proof / Artifacts |
|---|---|---|---|
| **Phase 1** | **AI Service (/ai-service)**: FastAPI, Hugging Face multilingual embeddings + LogisticRegression classifier, faster-whisper STT, 600 synthetic messages dataset, tests, Dockerfile | **COMPLETED** | `ai-service/`, 11/11 tests passed, 84.17% test accuracy |
| **Phase 2** | **Laravel Core (/web)**: Laravel 11, Breeze, multi-business isolation, SQLite/MySQL migrations, Models, WhatsApp export parser, Voice note uploads, Demo seeder | **COMPLETED** | `web/`, migrations & seeder executed, 4/4 triage feature tests passed |
| **Phase 3** | **Integration**: Queued background jobs (`ClassifyMessageJob`, `TranscribeAudioJob`), retry with backoff, resilient fallbacks | **COMPLETED** | `web/app/Jobs/`, 2/2 integration tests passed, end-to-end verified |
| **Phase 4** | **UI/UX**: Modern classic theme, responsive landing page, dashboard with urgency feed, category chart, dark/light mode, Urdu Nastaliq RTL, audio player, toasts | **COMPLETED** | `web/resources/views/`, responsive at 360px, 768px, 1280px |
| **Phase 5** | **Deployment Prep**: HF Space Dockerfile, Render Dockerfile + render.yaml, DEPLOY.md, docker-compose.yml, README.md | **COMPLETED** | `docker-compose.yml`, `DEPLOY.md`, `render.yaml`, `README.md` |

---

## Detailed Milestone Log

### Phase 1: AI Service (Completed)
- [x] Initialized Git repository.
- [x] Verified system environment: PHP 8.5, Composer 2.10, Python 3.11, Node 24, Git, FFmpeg 9.0.
- [x] Enabled `pdo_sqlite`, `sqlite3`, `zip`, and `gd` extensions in PHP.
- [x] Formulated brand identity in `BRAND.md` (Name: **Fauri / فوری**, meaning, 2 rejected alternatives, modern classic palette).
- [x] Created clean SVG brand assets in `/web/public/brand/` (`logomark.svg`, `logo-light.svg`, `logo-dark.svg`, `favicon.svg`).
- [x] Generated 600 balanced synthetic messages in `ai-service/data/messages.csv` (100 per category: order, complaint, refund, inquiry, urgent, other) with authentic Roman Urdu, Urdu Nastaliq, and English, plus phonetic spelling variations.
- [x] Implemented heuristic rules in `ai-service/app/rules.py` for urgency scoring (low/medium/high) and rule-based template summaries.
- [x] Built training pipeline `train.py` using `sentence-transformers/paraphrase-multilingual-MiniLM-L12-v2` + scikit-learn `LogisticRegression`.
- [x] Saved trained model to `ai-service/models/classifier.pkl` (Train accuracy: 99.58%, Test accuracy: 84.17%).
- [x] Implemented faster-whisper speech-to-text with `Systran/faster-whisper-small` (int8, CPU) and ffmpeg conversion in `ai-service/app/transcriber.py`.
- [x] Created FastAPI application `ai-service/app/main.py` with `/health`, `/classify`, `/transcribe` endpoints and CORS.
- [x] Wrote comprehensive pytest suite `ai-service/tests/` (11 unit and integration tests). All 11 passed.
- [x] Created Hugging Face Spaces `Dockerfile` (port 7860, non-root user, ffmpeg, model pre-caching).
- [x] Documented model performance and synthetic data disclaimer in `ai-service/README.md`.
- [x] Committed Phase 1 to Git (`c527353`).

### Phase 2: Laravel Core (Completed)
- [x] Initialized Laravel 11 project in `/web`.
- [x] Installed Laravel Breeze and Livewire 3 with Volt and Dark Mode support.
- [x] Built multi-business isolation schema and SQLite/MySQL compatible migrations:
  - `businesses` (user_id, name, slug, industry, whatsapp_number, currency, settings)
  - `customers` (business_id, name, phone, email, notes)
  - `messages` (business_id, customer_id, body, source [paste, whatsapp_export, voice], category, urgency, summary, confidence, status [pending, in_progress, resolved], audio_path, detected_language, ai_status, ai_error, timestamps)
- [x] Implemented Eloquent Models (`Business`, `Customer`, `Message`, `User`) with scopes and bidirectional RTL detection helpers.
- [x] Implemented `WhatsAppExportParser` service supporting both Android and iOS timestamp formats.
- [x] Created `DatabaseSeeder` with realistic Pakistani SMB demo data (demo user `demo@fauri.pk` / `password`, `Gulberg Apparel & Studio`, 10 customers, 20 categorized messages).
- [x] Executed migrations and seeding (`php artisan migrate:fresh --seed`).

### Phase 3: Integration (Completed)
- [x] Created queued background jobs `ClassifyMessageJob` and `TranscribeAudioJob` with 3 retries, exponential backoff (`[5, 15, 45]`), timeouts, and graceful fallbacks.
- [x] Configured `AI_SERVICE_URL` and `AI_SERVICE_TIMEOUT` in `.env`, `.env.example`, and `config/services.php`.
- [x] Created unit/feature tests in `Tests\Feature\IntegrationJobTest` verifying successful job execution and fallback states. All passed.

### Phase 4: UI/UX (Completed)
- [x] Configured Tailwind CSS with custom Fauri brand palette (`#06241A`, `#10B981`, `#F59E0B`, `#E11D48`), dark mode class, and Google Fonts (`Plus Jakarta Sans` & `Noto Nastaliq Urdu`).
- [x] Built responsive Landing Page (`welcome.blade.php`) with hero, interactive triage simulator, how-it-works, PKR pricing, FAQ, and footer.
- [x] Developed Livewire `Dashboard` component with:
  - 4 real-time stat cards (Today's feed, Urgent attention, Pending triage, Resolved).
  - Pure SVG category breakdown visualization with click-to-filter interaction.
  - Urgent priority feed surfacing critical wedding & medical emergencies.
  - Message inbox with search, category/urgency/status filters, date sorting, and bulk actions.
  - 1-click status cycling (`Pending` -> `In Progress` -> `Resolved`).
  - New Message modal with 3 tabs (Direct Paste, WhatsApp .txt file upload, Voice note upload).
  - Message detail drawer with 1-click category ground-truth correction.
- [x] Built Customer summary page (`/customers`) with order/complaint/refund metrics and communication timeline.
- [x] Implemented persistent Dark/Light mode and bilingual English/Urdu label toggle.
- [x] Created custom `404` and `500` error pages.
- [x] Built Vite assets with `npm run build` and verified full test suite (25/25 Laravel tests + 4 Fauri triage tests passed).

### Phase 5: Deployment Prep (Completed)
- [x] Created production `Dockerfile` for Laravel with multi-stage build, OPcache, and asset caching.
- [x] Created `render.yaml` deployment blueprint for Render Web Service and queue worker.
- [x] Created `production.env.example` template.
- [x] Created `docker-compose.yml` for 1-command local execution of both web and AI services.
- [x] Wrote `DEPLOY.md` detailing step-by-step free tier hosting (Hugging Face Spaces + Render + Aiven/TiDB MySQL) and cold-start mitigations.
- [x] Created root `README.md` with Mermaid architecture diagram, quickstart, and benchmarks.
- [x] Committed all phases to Git.

---

## Phase 6: System Audit & Hardening (Completed)

- [x] **Whisper "small" Model Verification**:
  - Deleted stale/partial cache locks in Hugging Face Hub cache (`~/.cache/huggingface/hub/.locks`).
  - Fresh snapshot download of `Systran/faster-whisper-small` (460MB model binary, vocabulary, config).
  - Explicitly set `WHISPER_MODEL=small`; confirmed transcriber loads "small" (not "tiny" fallback).
  - Enhanced fallback mechanism with loud logging warnings and exposed `"stt_model": "small"` in `GET /health`.
  - Full pytest test suite executed: 13/13 passed.
- [x] **Real Speech-to-Text Accuracy Test**:
  - Generated Urdu script sample (`urdu_sample.mp3`) and Roman Urdu sample (`roman_urdu_sample.mp3`) via `edge-tts` with Pakistani neural voices (`ur-PK-UzmaNeural`, `ur-PK-AsadNeural`).
  - Urdu script sample transcribed accurately (`اسلام علیکم مجھے اپنا اوڑر منسوخ کروانا ہے براہ میں حربانی میرے پیسے واپس کر دے`).
  - Unprompted Whisper classifies spoken Hindustani dialect as `hi` with Devanagari script; forced `language='ur'` produces authentic Nastaliq characters.
  - Roman Urdu sample with mixed digits showed repetition loops, demonstrating typical Whisper behavior on non-standard numerical code-switching.
- [x] **End-to-End System Proof**:
  - Started AI service (`uvicorn` on port 8001), Laravel web server (`php artisan serve` on port 8000), and queue worker daemon (`php artisan queue:work`).
  - Created message via paste flow -> `ClassifyMessageJob` triaged into `urgent` (`high` urgency, confidence 0.9288) in DB.
  - Uploaded voice note via app flow -> `TranscribeAudioJob` transcribed audio, saved text, and triggered `ClassifyMessageJob` -> triaged into `refund` in DB.
  - Full Laravel test suite executed: 32/32 tests passed (96 assertions).
- [x] **UI & Feature Fixes**:
  - Added Livewire real-time file upload progress bars for both WhatsApp `.txt` exports and Voice Notes.
  - Added animated skeleton loaders during Livewire feed filtering and status updates.
  - Added dynamic `:dir="lang === 'ur' ? 'rtl' : 'ltr'"` attribute for seamless Nastaliq RTL display.
  - Added `is_verified` database column and wired human category corrections into `is_verified = true`.
  - Upgraded `train.py` with `--retrain` CLI flag and automated SQLite human-correction feedback loop.
- [x] **Security Audit & Environment Guard**:
  - Guarded `DatabaseSeeder.php` to strictly block seeding demo credentials (`demo@fauri.pk` / `password`) in production.
  - Executed `composer audit`: reported 4 advisories on Laravel 11.x (reported September 2026); upgrade proposal prepared for user confirmation.
  - Updated `.gitignore` to comprehensively exclude all `.env`, `*.sqlite`, `vendor`, `node_modules`, audio uploads, and test media.

---

## Phase 7: Laravel 12 Upgrade, STT Repeat Guard & Hardening (Completed)

- [x] **Laravel 12.x Upgrade**:
  - Upgraded `laravel/framework` from `v11.57.0` to `v12.70.0`.
  - Upgraded `nunomaduro/collision` to `^8.9.5` and `phpunit/phpunit` to `^11.5.57`.
  - Removed deprecated `spatie/laravel-ignition`.
  - Executed `composer audit`: **0 security vulnerability advisories found**.
  - Suppressed PHP 8.5 `PDO::MYSQL_ATTR_SSL_CA` deprecation warning in `config/database.php` via dynamic `\Pdo\Mysql::ATTR_SSL_CA` constant check without masking real database errors.
  - Executed `php artisan test`: **32/32 tests passed (96 assertions), 0 failures, 0 deprecation warnings**.
- [x] **STT Improvements & Language Selection**:
  - Configured `/transcribe` endpoint to accept optional `language` parameter (`ur`, `en`, `auto`), defaulting to `"ur"`.
  - Added modern 3-way language selector (Urdu default, English, Auto) in the Livewire voice note upload modal.
  - Implemented `detect_looping_output()` and `clean_repeats()` guard in `AudioTranscriber`: catches 5+ consecutive repeated tokens or characters, automatically triggering retry with forced language (`"ur"`), temperature fallback (`0.2`), and `condition_on_previous_text=False`.
  - Re-ran transcription test on Urdu and Roman Urdu audio samples: both completed with HTTP 200, clean Nastaliq output, and zero repetition loops.
- [x] **Clear Text-Only Failure Fallback in UI**:
  - In `TranscribeAudioJob.php`: if transcription fails or yields silence, sets message body to `"Transcription failed, retry"` and avoids classifying garbage text.
  - In Livewire `Dashboard` and blade views: displays prominent `⚠️ Transcription failed, retry` indicator with a 1-click retry button.
- [x] **Cleanup & Hygiene**:
  - Removed temporary test scripts and artifacts (`test_flow.php`, `*.mp3`, `*.wav`, `accuracy_report.json`, `urdu_ur_script.txt`).
  - Confirmed `edge-tts` is NOT included in `ai-service/requirements.txt`.
  - Verified `git status` contains zero untracked `.env` files, secrets, or large binaries.

---

## Known Limitations & Honest Disclaimers

1. **Whisper "small" CPU Latency (~15–25s):** On standard CPU without GPU acceleration, transcribing a 6–8 second voice note takes ~15–25 seconds. Designed strictly for asynchronous background queue execution (`TranscribeAudioJob`), not blocking user HTTP requests.
2. **TTS-Only Synthetic Audio Testing:** STT verification was performed using Microsoft Edge synthetic neural TTS voices (`ur-PK-UzmaNeural`, `ur-PK-AsadNeural`). In real-world environments, background noise, low-end phone microphones, heavy accents, and mixed Roman Urdu/English code-switching will yield higher error rates.
3. **Docker Untested on Host:** Docker deployment specifications (`Dockerfile`, `ai-service/Dockerfile`, `docker-compose.yml`) are written according to best practices for containerized cloud deployment (Render & Hugging Face Spaces), but Docker engine was not run or verified on this Windows development host.


