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
│  (Laravel 12 + Livewire 3)    │ ────── │   (FastAPI CPU Microservice)  │
│                               │  HTTP  │                               │
│  • Web UI Dashboard & Ingest  │ Header │  • Faster-Whisper (Small int8)│
│  • Sync or Scheduled Queues   │ X-API- │  • Multilingual MiniLM L12-v2 │
│  • Multi-tenant Isolation     │  Key   │  • Heuristic Urgency Rules    │
└───────────────────────────────┘        └───────────────────────────────┘
             │                                           │
             ▼                                           ▼
┌───────────────────────────────┐              [HF Secret: AI_API_KEY]
│   Free Cloud MySQL (TiDB)     │
│   + Cloudflare R2 Audio       │
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

### Step 2: Configure Shared-Secret Auth
Under Space **Settings** &rarr; **Variables and secrets**:
1. Click **New secret**.
2. Name: `AI_API_KEY`
3. Value: Generate a random 32-character secret (e.g. `openssl rand -hex 16`).
4. Save. This shared secret ensures only your authorized Laravel backend can trigger classification and transcription requests. The `/health` probe remains public for monitoring.

### Step 3: Upload AI Service Files
Clone your newly created Space repository to your local machine:
```bash
git clone https://huggingface.co/spaces/<YOUR-USERNAME>/fauri-ai
cd fauri-ai
```

Copy the contents of `/ai-service` into your Space root:
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

### Step 4: Push to Hugging Face
```bash
git add .
git commit -m "Deploy Fauri AI Service with shared-secret auth"
git push origin main
```

### Step 5: Build & Endpoint Verification
- Hugging Face automatically builds the Docker container. Pre-cached embeddings (`MiniLM-L12-v2`) and Whisper small (`faster-whisper-small`) are ready in memory.
- Your public direct API endpoint will be:
  `https://<YOUR-USERNAME>-fauri-ai.hf.space`
- Verify the public health endpoint:
  ```bash
  curl -s https://<YOUR-USERNAME>-fauri-ai.hf.space/health
  # Expected: {"status":"ok","service":"fauri-ai","stt_model":"small",...}
  ```

---

## Part 2: Database & Storage Configuration

> [!WARNING]
> **Render Free Tier Ephemeral Filesystem Warning:**
> On Render's Free tier, the container filesystem is **ephemeral**. Any files created at runtime (such as local SQLite database `database/database.sqlite` or voice notes uploaded to `storage/app/public/voice_notes/`) **will be wiped** whenever the container spins down due to inactivity (after 15 minutes) or when a new deployment occurs.
> 
> For a temporary zero-setup demo, SQLite is supported out of the box. **For production, you must use an external free cloud MySQL database and cloud storage.**

### Option A: TiDB Cloud Serverless (Recommended Free MySQL)
TiDB Cloud provides a fully managed, MySQL 8.0-compatible Serverless tier with **5GB free storage and 50 million Request Units per month for free** with no credit card required:
1. Sign up at [tidbcloud.com](https://tidbcloud.com).
2. Click **Create Cluster** &rarr; Select **Serverless (Free)**.
3. Click **Connect** &rarr; Select **General** connection method.
4. Note your credentials and set the following Render environment variables:
   ```env
   DB_CONNECTION=mysql
   DB_HOST=gateway01.us-west-2.prod.aws.tidbcloud.com
   DB_PORT=4000
   DB_DATABASE=fauri
   DB_USERNAME=<YOUR_TIDB_USERNAME>
   DB_PASSWORD=<YOUR_TIDB_PASSWORD>
   ```

### Option B: Aiven Cloud MySQL (Alternative Free MySQL)
Aiven provides a free forever 5GB managed MySQL instance:
1. Sign up at [aiven.io](https://aiven.io) &rarr; Create a **Free MySQL** service.
2. Under Overview, copy the Host, Port (`3306`), and download the CA certificate (`ca.pem`).
3. Set the Render environment variables:
   ```env
   DB_CONNECTION=mysql
   DB_HOST=mysql-xxxx-your-project.aivencloud.com
   DB_PORT=3306
   DB_DATABASE=defaultdb
   DB_USERNAME=avnadmin
   DB_PASSWORD=<YOUR_AIVEN_PASSWORD>
   ```

### Persistent Voice Note Storage (Cloudflare R2 / AWS S3)
To ensure uploaded WhatsApp audio files persist across dyno restarts:
1. Create a free bucket on [Cloudflare R2](https://developers.cloudflare.com/r2/) (10GB free storage, zero egress fees).
2. Set the following environment variables on Render:
   ```env
   FILESYSTEM_DISK=s3
   AWS_ACCESS_KEY_ID=<YOUR_R2_ACCESS_KEY>
   AWS_SECRET_ACCESS_KEY=<YOUR_R2_SECRET_KEY>
   AWS_DEFAULT_REGION=auto
   AWS_BUCKET=fauri-audio
   AWS_ENDPOINT=https://<YOUR_ACCOUNT_ID>.r2.cloudflarestorage.com
   ```

---

## Part 3: Deploy Laravel Web Application on Render.com (100% Free Tier)

Render's free tier supports Docker web services. **Background worker services (`type: worker`) require a paid starter plan ($7/mo)**. Fauri is engineered to run 100% on the Free tier using two options:

### Queue Processing Modes on Free Tier:

| Mode | Configuration | How It Works | Best For |
|---|---|---|---|
| **Demo Mode (Default)** | `QUEUE_CONNECTION=sync` | Triage and transcription jobs execute immediately within the web request without needing any background worker. | Live interactive demos, testing, and low-traffic shops. |
| **Scheduled Worker Mode** | `QUEUE_CONNECTION=database` | Jobs are stored in the database. A free external scheduler or Render cron triggers `php artisan queue:work --stop-when-empty --max-time=50` every 5-10 minutes. | Medium-volume async processing with zero worker fees. |

### Step 1: Push Repository to GitHub
Push your repository containing `/web` and the updated `render.yaml` to GitHub.

### Step 2: Deploy via Blueprint or Manual Web Service
1. Log in to [Render.com](https://render.com).
2. Click **New +** &rarr; Select **Blueprint** (connect repo to deploy from `render.yaml`) OR click **Web Service**:
   - **Name:** `fauri-web`
   - **Environment:** `Docker`
   - **Dockerfile Path:** `./web/Dockerfile`
   - **Docker Context:** `./web`
   - **Instance Type:** `Free`

### Step 3: Configure Environment Variables in Render Dashboard
Add the following key-value pairs:

| Variable | Value / Description |
|---|---|
| `APP_NAME` | `Fauri` |
| `APP_ENV` | `production` |
| `APP_DEBUG` | `false` |
| `APP_KEY` | `base64:...` *(Generate with `php artisan key:generate --show`)* |
| `APP_URL` | `https://fauri-web.onrender.com` |
| `DB_CONNECTION` | `mysql` *(or `sqlite` for temporary demo)* |
| `DB_HOST` | `<your-cloud-db-host>` |
| `DB_PORT` | `4000` *(TiDB) or `3306` (Aiven)* |
| `DB_DATABASE` | `fauri` |
| `DB_USERNAME` | `<db-user>` |
| `DB_PASSWORD` | `<db-password>` |
| `SESSION_DRIVER` | `database` |
| `QUEUE_CONNECTION` | `sync` *(instant demo)* or `database` *(scheduled worker)* |
| `CACHE_STORE` | `database` |
| `AI_SERVICE_URL` | `https://<YOUR-USERNAME>-fauri-ai.hf.space` |
| `AI_SERVICE_KEY` | `<your-shared-secret>` *(must match `AI_API_KEY` set in Hugging Face)* |
| `AI_SERVICE_TIMEOUT` | `35` |

### Step 4: Run Migrations in Render Shell
Once deployed, open the Render **Shell** tab for `fauri-web`:
```bash
php artisan migrate --force
```

---

## Part 4: Post-Deployment Smoke Test

1. **Verify AI Microservice Auth & Health:**
   ```bash
   # Public health check:
   curl -s https://<YOUR-USERNAME>-fauri-ai.hf.space/health
   # Expected: {"status":"ok","service":"fauri-ai",...}

   # Unauthenticated call (rejected):
   curl -s -X POST https://<YOUR-USERNAME>-fauri-ai.hf.space/classify -H "Content-Type: application/json" -d '{"text":"test"}'
   # Expected: {"error":"unauthorized",...}

   # Authenticated call (accepted):
   curl -s -X POST https://<YOUR-USERNAME>-fauri-ai.hf.space/classify \
     -H "Content-Type: application/json" \
     -H "X-API-Key: <YOUR_SECRET>" \
     -d '{"text":"bhai mera parcel kab dispatch hoga"}'
   # Expected: {"category":"inquiry","urgency":"medium",...}
   ```

2. **Verify Laravel Web Dashboard:**
   Navigate to `https://fauri-web.onrender.com`.
   - Register your store or log in.
   - Paste a Roman Urdu message or upload an audio voice note.
   - The message is authenticated via `X-API-Key`, triaged by Fauri AI, and surfaced in the dashboard in real time with Nastaliq RTL formatting.
