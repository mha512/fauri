# Fauri (فوری) - Free Tier Production Deployment Guide

Deploy a complete, production-grade installation of Fauri without spending a single dollar on cloud hosting or proprietary LLM APIs.

---

## Architecture Blueprint

```text
[Customer WhatsApp / Voice Note]
             │
             ▼
┌───────────────────────────────┐        ┌───────────────────────────────┐
│        Render.com             │        │     Hugging Face Spaces       │
│  (Laravel 11 + Livewire 3)    │ ────── │   (FastAPI CPU Microservice)  │
│                               │  HTTP  │                               │
│  • Web UI Dashboard & Ingest  │        │  • Faster-Whisper (Small int8)│
│  • Database Background Queues │        │  • Multilingual MiniLM L12-v2 │
│  • Multi-tenant Isolation     │        │  • Heuristic Urgency Rules    │
└───────────────────────────────┘        └───────────────────────────────┘
             │
             ▼
┌───────────────────────────────┐
│     SQLite or Free MySQL      │
│  (Aiven / TiDB / PlanetScale) │
└───────────────────────────────┘
```

---

## Part 1: Deploy AI Microservice on Hugging Face Spaces (Free Tier)

Hugging Face provides free 2 vCPU, 16GB RAM Docker instances with no credit card required.

### Step 1: Create Space
1. Log in to [Hugging Face](https://huggingface.co).
2. Click **New Space** &rarr; Name it `fauri-ai`.
3. Space SDK: Select **Docker** (Blank).
4. License: `MIT` &bull; Visibility: **Public** (or Private with an access token).

### Step 2: Upload AI Service Files
Clone your newly created Space repository to your local machine:
```bash
git clone https://huggingface.co/spaces/<YOUR-USERNAME>/fauri-ai
cd fauri-ai
```

Copy the contents of the `/ai-service` directory into your Space root:
```bash
# Files to copy:
Dockerfile
requirements.txt
train.py
app/
  __init__.py
  main.py
  classifier.py
  transcriber.py
  rules.py
data/
  generate_dataset.py
  messages.csv
models/
  classifier.pkl
```

### Step 3: Push to Hugging Face
```bash
git add .
git commit -m "Deploy Fauri AI Service"
git push origin main
```

### Step 4: Build & URL Generation
- Hugging Face automatically executes the `Dockerfile`.
- During build, `Systran/faster-whisper-small` and `sentence-transformers/paraphrase-multilingual-MiniLM-L12-v2` are downloaded and cached directly into `/home/user/.cache/huggingface`.
- Your public direct API endpoint will be:
  `https://<YOUR-USERNAME>-fauri-ai.hf.space`

### Cold-Start Notes & Mitigation
> [!NOTE]
> **Free Space Sleeping Behavior:**
> Free Hugging Face Spaces enter sleep mode after 48 hours of complete inactivity. When an incoming request arrives, the space wakes up in approximately 15-20 seconds.
> 
> **How Fauri handles this gracefully:**
> 1. Fauri does not block the web UI; classification and transcription execute in Laravel database queues (`ClassifyMessageJob`).
> 2. Jobs are configured with 3 retries and exponential backoff (`$backoff = [5, 15, 45]`).
> 3. If the AI space is waking up on the first retry, subsequent retries succeed seamlessly once the container is alive.
> 4. If the service is unreachable after 3 attempts, the message status is marked as `unclassified` with a 1-click **Retry** button in the dashboard.

---

## Part 2: Database Options

### Option A: Embedded SQLite (Simplest, Zero Cost)
- Laravel 11 runs out-of-the-box on SQLite (`database/database.sqlite`).
- Suitable for small businesses triaging < 2,000 messages per month.
- Ensure the database file resides on a persistent directory or volume.

### Option B: Free Cloud MySQL / Postgres (Recommended for Scalability)
- **Aiven Cloud**: Free 5GB MySQL database instance.
- **TiDB Serverless**: Free 5GB MySQL-compatible database instance.
- Connection string in `.env`:
  ```env
  DB_CONNECTION=mysql
  DB_HOST=your-instance.aivencloud.com
  DB_PORT=3306
  DB_DATABASE=fauri
  DB_USERNAME=avnadmin
  DB_PASSWORD=your_password
  ```

---

## Part 3: Deploy Laravel Web Application on Render.com (Free Tier)

Render offers free web services that run Docker containers natively.

### Step 1: Push Repository to GitHub
Push your project containing `/web` and `render.yaml` to GitHub.

### Step 2: Create Web Service on Render
1. Log in to [Render.com](https://render.com).
2. Click **New +** &rarr; **Web Service**.
3. Connect your GitHub repository.
4. Settings:
   - **Name:** `fauri-web`
   - **Region:** Oregon (US West) or Frankfurt (EU)
   - **Environment:** `Docker`
   - **Dockerfile Path:** `./web/Dockerfile`
   - **Docker Context:** `./web`
   - **Instance Type:** `Free`

### Step 3: Configure Environment Variables in Render Dashboard
Add the following key-value pairs under **Environment**:

| Key | Value / Guidance |
|---|---|
| `APP_NAME` | `Fauri` |
| `APP_ENV` | `production` |
| `APP_DEBUG` | `false` |
| `APP_KEY` | *(Generate via `php artisan key:generate --show` or Render auto-value)* |
| `APP_URL` | `https://fauri-web.onrender.com` *(your assigned Render URL)* |
| `DB_CONNECTION` | `sqlite` *(or `mysql` if using Aiven)* |
| `DB_DATABASE` | `/var/www/html/database/database.sqlite` |
| `SESSION_DRIVER` | `database` |
| `QUEUE_CONNECTION` | `database` |
| `CACHE_STORE` | `database` |
| `AI_SERVICE_URL` | `https://<YOUR-USERNAME>-fauri-ai.hf.space` *(from Part 1)* |
| `AI_SERVICE_TIMEOUT` | `35` |

### Step 4: Run Migrations and Seed Demo Data
Once deployed, open the Render **Shell** tab for `fauri-web`:
```bash
# Run production migrations
php artisan migrate --force

# (Optional) Seed demo user and messages
php artisan db:seed --force
```

---

## Part 4: Post-Deployment Smoke Test

1. **Verify AI Microservice:**
   ```bash
   curl -s https://<YOUR-USERNAME>-fauri-ai.hf.space/health
   # Expected: {"status":"ok","service":"fauri-ai",...}
   ```
2. **Verify Laravel Web Application:**
   Navigate to `https://fauri-web.onrender.com`.
   - The landing page loads with the Fauri logomark, interactive triage simulator, and pricing in PKR.
   - Click **Log in** &rarr; Enter `demo@fauri.pk` / `password`.
   - The dashboard opens with stat cards, category breakdown, and Urdu RTL support.
