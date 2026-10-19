"""
train.py
Trains the Fauri message classification model.
1. Loads synthetic messages from data/messages.csv
2. If --retrain flag is set, queries human-verified messages (is_verified = 1 or confidence = 1.0)
   from the production/local SQLite database to incorporate real correction feedback.
3. Computes embeddings using 'sentence-transformers/paraphrase-multilingual-MiniLM-L12-v2'
4. Fits a scikit-learn LogisticRegression classifier
5. Evaluates accuracy and outputs a classification report
6. Saves classifier.pkl and metadata to models/
"""

import os
import sys
import time
import sqlite3
import argparse
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

# SQLite database path (in Laravel web/database/database.sqlite)
DEFAULT_SQLITE_PATH = os.path.abspath(os.path.join(BASE_DIR, "..", "web", "database", "database.sqlite"))

EMBEDDING_MODEL_NAME = "sentence-transformers/paraphrase-multilingual-MiniLM-L12-v2"

def load_verified_database_samples(db_path: str) -> pd.DataFrame:
    """
    Fetches human-verified corrections from the SQLite database.
    """
    if not os.path.exists(db_path):
        print(f"[Retrain Loop] Database not found at: {db_path}. Skipping DB samples.")
        return pd.DataFrame(columns=["text", "label"])

    try:
        conn = sqlite3.connect(db_path)
        # Fetch columns from messages table
        query = """
            SELECT body AS text, category AS label
            FROM messages
            WHERE (is_verified = 1 OR confidence >= 0.999)
              AND category IN ('order', 'complaint', 'refund', 'urgent', 'inquiry', 'other')
              AND body IS NOT NULL
              AND trim(body) != ''
        """
        verified_df = pd.read_sql_query(query, conn)
        conn.close()
        print(f"[Retrain Loop] Found {len(verified_df)} verified human corrections in SQLite database.")
        return verified_df
    except Exception as e:
        print(f"[Retrain Loop] Warning: Could not query verified samples from {db_path}: {e}")
        return pd.DataFrame(columns=["text", "label"])

def main():
    parser = argparse.ArgumentParser(description="Train or retrain Fauri multilingual message classifier.")
    parser.add_argument("--retrain", action="store_true", help="Incorporate is_verified corrections from SQLite DB into training set.")
    parser.add_argument("--db-path", type=str, default=DEFAULT_SQLITE_PATH, help="Path to SQLite database.")
    args = parser.parse_args()

    print("=" * 60)
    mode_str = "ACTIVE CORRECTION RETRAIN PIPELINE" if args.retrain else "BASELINE TRAINING PIPELINE"
    print(f"FAURI AI CLASSIFIER - {mode_str}")
    print("=" * 60)

    if not os.path.exists(DATA_PATH):
        raise FileNotFoundError(f"Dataset not found at {DATA_PATH}. Run generate_dataset.py first.")

    df = pd.read_csv(DATA_PATH)
    print(f"Loaded {len(df)} base samples from {DATA_PATH}")

    if args.retrain:
        verified_df = load_verified_database_samples(args.db_path)
        if len(verified_df) > 0:
            print(f"Augmenting base dataset ({len(df)}) with {len(verified_df)} verified user corrections...")
            df = pd.concat([df, verified_df], ignore_index=True)
            print(f"Total training samples after merge: {len(df)}")
        else:
            print("No new verified samples found; retraining on existing dataset.")

    print("\nClass distribution:\n", df['label'].value_counts())

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

    # Save artifact
    os.makedirs(MODELS_DIR, exist_ok=True)
    payload = {
        "classifier": clf,
        "classes": clf.classes_.tolist(),
        "embedding_model": EMBEDDING_MODEL_NAME,
        "train_accuracy": float(train_acc),
        "test_accuracy": float(test_acc),
        "trained_at": time.strftime("%Y-%m-%d %H:%M:%S UTC", time.gmtime()),
        "is_retrained": bool(args.retrain),
    }
    joblib.dump(payload, MODEL_FILE)
    print(f"\nModel saved successfully to: {MODEL_FILE}")

    with open(REPORT_FILE, "w", encoding="utf-8") as f:
        f.write(f"Fauri Classifier Training Report\n")
        f.write(f"Model: {EMBEDDING_MODEL_NAME} + LogisticRegression\n")
        f.write(f"Mode: {'Retrained with DB feedback' if args.retrain else 'Baseline'}\n")
        f.write(f"Train Accuracy: {train_acc * 100:.2f}%\n")
        f.write(f"Test Accuracy: {test_acc * 100:.2f}%\n\n")
        f.write(report)

    print(f"Evaluation report saved to: {REPORT_FILE}")

if __name__ == "__main__":
    main()
