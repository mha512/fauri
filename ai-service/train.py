"""
train.py
Trains the Fauri message classification model.
1. Loads 600 balanced synthetic messages from data/messages.csv
2. Computes embeddings using 'sentence-transformers/paraphrase-multilingual-MiniLM-L12-v2'
3. Fits a scikit-learn LogisticRegression classifier
4. Evaluates accuracy and outputs a classification report
5. Saves classifier.pkl and metadata to models/
"""

import os
import time
import joblib
import pandas as pd
import numpy as np
from sklearn.model_selection import train_test_split
from sklearn.linear_model import LogisticRegression
from sklearn.metrics import classification_report, accuracy_score
from sentence_transformers import SentenceTransformer

BASE_DIR = os.path.dirname(os.path.abspath(__file__))
DATA_PATH = os.path.join(BASE_DIR, "data", "messages.csv")
MODELS_DIR = os.path.join(BASE_DIR, "models")
MODEL_FILE = os.path.join(MODELS_DIR, "classifier.pkl")
REPORT_FILE = os.path.join(MODELS_DIR, "evaluation_report.txt")

EMBEDDING_MODEL_NAME = "sentence-transformers/paraphrase-multilingual-MiniLM-L12-v2"

def main():
    print("=" * 60)
    print("FAURI AI CLASSIFIER - TRAINING PIPELINE")
    print("=" * 60)

    if not os.path.exists(DATA_PATH):
        raise FileNotFoundError(f"Dataset not found at {DATA_PATH}. Run generate_dataset.py first.")

    df = pd.read_csv(DATA_PATH)
    print(f"Loaded {len(df)} samples from {DATA_PATH}")
    print("Class distribution:\n", df['label'].value_counts())

    print(f"\nLoading embedding model: {EMBEDDING_MODEL_NAME}...")
    t0 = time.time()
    embedder = SentenceTransformer(EMBEDDING_MODEL_NAME)
    print(f"Embedding model loaded in {time.time() - t0:.2f}s")

    print("Generating multilingual sentence embeddings (batch size 32)...")
    t0 = time.time()
    embeddings = embedder.encode(df['text'].tolist(), show_progress_bar=True, batch_size=32)
    print(f"Embeddings generated in {time.time() - t0:.2f}s, shape: {embeddings.shape}")

    X = embeddings
    y = df['label'].values

    # Stratified 80/20 split
    X_train, X_test, y_train, y_test = train_test_split(
        X, y, test_size=0.2, random_state=42, stratify=y
    )
    print(f"Train samples: {len(X_train)}, Test samples: {len(X_test)}")

    print("\nTraining LogisticRegression classifier...")
    clf = LogisticRegression(max_iter=1000, C=2.0, class_weight='balanced', random_state=42)
    clf.fit(X_train, y_train)

    train_preds = clf.predict(X_train)
    test_preds = clf.predict(X_test)

    train_acc = accuracy_score(y_train, train_preds)
    test_acc = accuracy_score(y_test, test_preds)
    report = classification_report(y_test, test_preds, digits=4)

    print("\n" + "=" * 60)
    print(f"TRAIN ACCURACY: {train_acc * 100:.2f}%")
    print(f"TEST ACCURACY:  {test_acc * 100:.2f}%")
    print("=" * 60)
    print("\nCLASSIFICATION REPORT (TEST SET):\n")
    print(report)
    print("=" * 60)
    print("NOTE ON REAL-WORLD PERFORMANCE:")
    print("Synthetic data accuracy is optimistic. Real customer WhatsApp messages contain")
    print("extensive audio slang, heavy code-switching, incomplete sentences, and novel acronyms.")
    print("Periodic retraining on authentic business messages is recommended.")
    print("=" * 60)

    # Save artifact
    os.makedirs(MODELS_DIR, exist_ok=True)
    payload = {
        "classifier": clf,
        "classes": clf.classes_.tolist(),
        "embedding_model": EMBEDDING_MODEL_NAME,
        "train_accuracy": float(train_acc),
        "test_accuracy": float(test_acc),
        "trained_at": time.strftime("%Y-%m-%d %H:%M:%S UTC", time.gmtime()),
    }
    joblib.dump(payload, MODEL_FILE)
    print(f"\nModel saved successfully to: {MODEL_FILE}")

    with open(REPORT_FILE, "w", encoding="utf-8") as f:
        f.write(f"Fauri Classifier Training Report\n")
        f.write(f"Model: {EMBEDDING_MODEL_NAME} + LogisticRegression\n")
        f.write(f"Train Accuracy: {train_acc * 100:.2f}%\n")
        f.write(f"Test Accuracy: {test_acc * 100:.2f}%\n\n")
        f.write(report)

    print(f"Evaluation report saved to: {REPORT_FILE}")

if __name__ == "__main__":
    main()
