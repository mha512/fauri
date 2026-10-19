import os
import sys
import json

# Ensure UTF-8 output on Windows
if sys.platform == "win32":
    import io
    sys.stdout = io.TextIOWrapper(sys.stdout.buffer, encoding="utf-8")
    sys.stderr = io.TextIOWrapper(sys.stderr.buffer, encoding="utf-8")

os.environ["WHISPER_MODEL"] = "small"
from app.transcriber import AudioTranscriber

def run_tests():
    t = AudioTranscriber("small")
    print(f"Active STT Model: {t.model_size}")

    test_cases = [
        {
            "id": "urdu_script",
            "file": "urdu_sample.mp3",
            "ground_truth": "السلام علیکم، مجھے اپنا آرڈر منسوخ کروانا ہے، برائے مہربانی میرے پیسے واپس کر دیں۔",
            "expected_language": "ur"
        },
        {
            "id": "roman_urdu",
            "file": "roman_urdu_sample.mp3",
            "ground_truth": "Bhai mera order number 54321 hai, yeh abhi tak deliver kyun nahi hua? Bohat emergency hai.",
            "expected_language": "ur"
        }
    ]

    results = []
    for tc in test_cases:
        path = os.path.join(os.path.dirname(__file__), tc["file"])
        if not os.path.exists(path):
            print(f"File not found: {path}")
            continue

        with open(path, "rb") as f:
            audio_bytes = f.read()

        res = t.transcribe(audio_bytes, original_filename=tc["file"])
        record = {
            "id": tc["id"],
            "file": tc["file"],
            "ground_truth": tc["ground_truth"],
            "transcript": res.get("text", ""),
            "detected_language": res.get("language", ""),
            "language_probability": res.get("language_probability", 0.0),
            "duration": res.get("duration", 0.0)
        }
        results.append(record)
        print("--------------------------------------------------")
        print(f"Sample: {tc['id']} ({tc['file']})")
        print(f"Ground Truth:        {record['ground_truth']}")
        print(f"Detected Transcript: {record['transcript']}")
        print(f"Detected Language:   {record['detected_language']} (probability: {record['language_probability']})")
        print(f"Audio Duration:      {record['duration']}s")

    with open(os.path.join(os.path.dirname(__file__), "accuracy_report.json"), "w", encoding="utf-8") as out:
        json.dump(results, out, ensure_ascii=False, indent=2)
    print("--------------------------------------------------")
    print("Results saved to accuracy_report.json")

if __name__ == "__main__":
    run_tests()
