"""
Unit tests for Fauri heuristic rules: urgency detection and rule-based summaries.
"""

import pytest
from app.rules import detect_urgency, generate_summary

def test_high_urgency_detection():
    # Urdu script
    urdu_urgent = "فوری ایمرجنسی ہے مریض کی حالت بہت خراب ہے"
    assert detect_urgency(urdu_urgent, "urgent") == "high"

    # Roman Urdu
    roman_urgent = "bhai please emergency hai call pick karein foran"
    assert detect_urgency(roman_urgent, "urgent") == "high"

    # English
    en_urgent = "CRITICAL EMERGENCY: Patient having severe chest pain"
    assert detect_urgency(en_urgent, "urgent") == "high"

    # Complaint with high urgency keyword
    fraud_complaint = "bhai police case ban raha hai fraud hua hai mere sath"
    assert detect_urgency(fraud_complaint, "complaint") == "high"

def test_medium_urgency_detection():
    # Tracking inquiry with delay
    msg = "bhai mera parcel kab tak dispatch hoga tracking number de dain"
    assert detect_urgency(msg, "inquiry") == "medium"

    # Complaint without emergency words
    complaint_msg = "size chota bhej dia hai change kar k do"
    assert detect_urgency(complaint_msg, "complaint") in ["medium", "high"]

    # Refund request
    refund_msg = "paise wapas transfer kar dein return deliver ho gaya hai"
    assert detect_urgency(refund_msg, "refund") in ["medium", "high"]

def test_low_urgency_detection():
    greeting = "Assalam o Alaikum bhai, kya hal hain? JazakAllah for the package"
    assert detect_urgency(greeting, "other") == "low"

    general_inquiry = "kya aap k pas wholesale rate pe mil sakta hai?"
    assert detect_urgency(general_inquiry, "inquiry") == "low"

def test_summary_generation():
    # Order
    order_sum = generate_summary("bhai mujhe black suit medium size order karna hai order #1042", "order", "low")
    assert "New Order Request" in order_sum
    assert "#1042" in order_sum

    # Refund with amount
    refund_sum = generate_summary("mera order cancel kr k 4500 PKR wapas kar dain", "refund", "medium")
    assert "Refund/Return Request" in refund_sum
    assert "4,500" in refund_sum or "4500" in refund_sum

    # Urgent
    urgent_sum = generate_summary("EMERGENCY call dr asim baby having fits", "urgent", "high")
    assert "CRITICAL / URGENT" in urgent_sum
