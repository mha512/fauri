# Dockerfile for Hugging Face Spaces (Port 7860, CPU-only, Free Tier Friendly)
FROM python:3.11-slim

# Prevent Python from writing .pyc files and enable unbuffered output
ENV PYTHONDONTWRITEBYTECODE=1 \
    PYTHONUNBUFFERED=1 \
    PORT=7860 \
    HF_HOME=/home/user/.cache/huggingface

# Install system dependencies including ffmpeg
RUN apt-get update && apt-get install -y --no-install-recommends \
    ffmpeg \
    git \
    curl \
    build-essential \
    && rm -rf /var/lib/apt/lists/*

# Set up non-root user matching Hugging Face Spaces specification (user with UID 1000)
RUN useradd -m -u 1000 user
USER user
ENV HOME=/home/user \
    PATH=/home/user/.local/bin:$PATH

WORKDIR /app

# Copy dependency definition and install
COPY --chown=user:user requirements.txt /app/requirements.txt
RUN pip install --no-cache-dir --upgrade pip && \
    pip install --no-cache-dir -r requirements.txt

# Pre-download models during build phase so startup is instantaneous
RUN python -c "from sentence_transformers import SentenceTransformer; print('Pre-caching MiniLM...'); SentenceTransformer('sentence-transformers/paraphrase-multilingual-MiniLM-L12-v2', device='cpu')" && \
    python -c "from faster_whisper import WhisperModel; print('Pre-caching Whisper small int8...'); WhisperModel('small', device='cpu', compute_type='int8')"

# Copy application files, data, and trained models
COPY --chown=user:user . /app/

# Expose Hugging Face Space default port
EXPOSE 7860

# Launch FastAPI server via uvicorn
CMD ["uvicorn", "app.main:app", "--host", "0.0.0.0", "--port", "7860", "--workers", "1"]
