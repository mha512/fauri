"""
rules.py
Heuristic rules for:
1. Urgency Detection (low, medium, high) combining Roman Urdu, Urdu script, and English keywords + predicted category.
2. Rule-based summary generation (no external LLM calls).
"""

import re
from typing import Dict, Any

# Urgency keywords
HIGH_URGENCY_KEYWORDS = [
    # English
    r"\burgent\b", r"\burgently\b", r"\bemergency\b", r"\bimmediately\b", r"\basap\b",
    r"\bcritical\b", r"\bpolice\b", r"\bfraud\b", r"\bscam\b", r"\blife\b", r"\bhospital\b",
    r"\bleak\b", r"\bbleeding\b", r"\bseizure\b", r"\binjury\b", r"\bpoison\b", r"\bicu\b",
    r"\bcourt\b", r"\blawyer\b", r"\blegal\b", r"\bthreat\b",
    # Roman Urdu
    r"\bforan\b", r"\bforun\b", r"\bfauri\b", r"\bjaldi\b", r"\bjld\b",
    r"\bkhuda k waste\b", r"\bkhuda ke waste\b", r"\bzindagi\b", r"\bmaut\b",
    r"\bhospital\b", r"\bdoctor sb\b", r"\bdr sb\b", r"\btabiyat khrab\b", r"\btabiyat boht kharab\b",
    r"\broko\b", r"\bstop\b", r"\bfraud\b", r"\bdhoka\b", r"\bpolice\b", r"\bpolice case\b",
    r"\bhack\b", r"\bcompromise\b", r"\bshadi h kal\b", r"\bshadi hai kal\b", r"\bflight\b",
    r"\baaj hi\b", r"\baj hi\b", r"\bhar hal me\b", r"\bhar soorat\b", r"\bnuksan\b",
    # Urdu Script
    r"فوری", r"ایمرجنسی", r"ہنگامی", r"خدا کے لیے", r"خدا کے واسطے", r"زندگی اور موت",
    r"نازک حالت", r"ہسپتال", r"پولیس", r"فراڈ", r"دھوکہ", r"آج ہی", r"فوراً", r"شدید",
    r"قانونی کارروائی", r"وارننگ", r"آئی سی یو"
]

MEDIUM_URGENCY_KEYWORDS = [
    # English
    r"\bcomplaint\b", r"\bdelayed\b", r"\bdelay\b", r"\bbroken\b", r"\bdamaged\b",
    r"\bmissing\b", r"\bdefective\b", r"\bwrong item\b", r"\brefund\b", r"\bcancel\b",
    r"\btracking\b", r"\bcourier\b", r"\brude\b", r"\bexpired\b", r"\bdispute\b",
    # Roman Urdu
    r"\bkab tak\b", r"\bkb tk\b", r"\bkab milega\b", r"\btracking\b", r"\btoota\b",
    r"\bkharab\b", r"\bkhraab\b", r"\bphatta\b", r"\bgalat\b", r"\bghalat\b",
    r"\bmissing\b", r"\bpaise wapas\b", r"\bpesay wapis\b", r"\brefund\b", r"\bcancel\b",
    r"\bexchange\b", r"\bchange\b", r"\blate\b", r"\bder\b", r"\bseen kar k chore dia\b",
    r"\breply nahi\b", r"\breply ni\b", r"\bcall pick nai\b", r"\bdissatisfied\b",
    # Urdu Script
    r"خراب", r"ٹوٹا", r"غلط", r"واپسی", r"پیسے واپس", r"ریفنڈ", r"کینسل", r"تاخیر",
    r"کب تک", r"ٹریکنگ", r"گم", r"شکایت", r"ناقص", r"بدتمیزی"
]

LOW_URGENCY_KEYWORDS = [
    # English
    r"\bthanks\b", r"\bthank you\b", r"\bjazakallah\b", r"\bhello\b", r"\bhi\b",
    r"\bgood morning\b", r"\bwhat is the price\b", r"\bhow much\b", r"\bavailable\b",
    # Roman Urdu
    r"\bshukriya\b", r"\bjazakallah\b", r"\baoa\b", r"\bsalam\b", r"\bkya hal\b",
    r"\bprice kya ha\b", r"\brate btaen\b", r"\bfabric konsa ha\b", r"\bstock me ha\b",
    # Urdu Script
    r"شکریہ", r"جزاک اللہ", r"سلام", r"السلام علیکم", r"قیمت", r"معلومات", r"کیا حال ہے"
]

def detect_urgency(text: str, category: str) -> str:
    """
    Computes urgency (low, medium, high) using keyword density + predicted category.
    """
    text_lower = text.lower()

    # Direct category overrides
    if category == "urgent":
        return "high"

    # Count high matches
    high_score = 0
    for pattern in HIGH_URGENCY_KEYWORDS:
        if re.search(pattern, text_lower, re.IGNORECASE):
            high_score += 1

    if high_score >= 1:
        return "high"

    # Count medium matches
    med_score = 0
    for pattern in MEDIUM_URGENCY_KEYWORDS:
        if re.search(pattern, text_lower, re.IGNORECASE):
            med_score += 1

    if category in ["complaint", "refund"]:
        # If it's a complaint or refund, default is at least medium
        return "high" if med_score >= 2 else "medium"

    if med_score >= 1:
        return "medium"

    if category == "order":
        return "medium" if "urgent" in text_lower or "kal" in text_lower or "aaj" in text_lower else "low"

    return "low"

def generate_summary(text: str, category: str, urgency: str) -> str:
    """
    Produces a crisp, informative rule-based summary without external LLMs.
    Extracts key tokens (amount, order IDs, phone/tracking numbers, key issue).
    """
    clean_text = " ".join(text.split()).strip()

    # Extract potential PKR amounts
    amount_match = re.search(r"(?:rs\.?|pkr|rupees|روپے)\s*([0-9,]+)", clean_text, re.IGNORECASE)
    if not amount_match:
        amount_match = re.search(r"([0-9,]+)\s*(?:rs|pkr|rupees|روپے)", clean_text, re.IGNORECASE)
    amount_str = f" (PKR {amount_match.group(1)})" if amount_match else ""

    # Extract tracking or order reference if present
    track_match = re.search(r"(?:order|tracking|#)\s*([0-9A-Za-z\-]{4,15})", clean_text, re.IGNORECASE)
    track_str = f" [Ref #{track_match.group(1)}]" if track_match else ""

    # Extract brief context snippet (first 60-80 chars)
    snippet = clean_text if len(clean_text) <= 75 else clean_text[:72] + "..."

    if category == "order":
        return f"New Order Request{track_str}: {snippet}"
    elif category == "complaint":
        return f"Customer Complaint - Needs Attention: {snippet}"
    elif category == "refund":
        return f"Refund/Return Request{amount_str}: {snippet}"
    elif category == "urgent":
        return f"CRITICAL / URGENT: {snippet}"
    elif category == "inquiry":
        return f"Product/Service Inquiry: {snippet}"
    else:
        return f"General Message: {snippet}"
