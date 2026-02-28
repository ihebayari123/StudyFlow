# ai/generate.py

import sys
import json
import re
import fitz
import requests

# ✅ Fix Windows encoding
sys.stdout.reconfigure(encoding='utf-8')

pdf_path  = sys.argv[1]
output_path = sys.argv[2]
quiz_id   = int(sys.argv[3])

# ── استخراج النص من PDF ──
doc = fitz.open(pdf_path)
raw_text = ""
for page in doc:
    raw_text += page.get_text()

# ── Chunking ──
def split_chunks(text, size=500, overlap=50):
    words = text.split()
    chunks = []
    for i in range(0, len(words), size - overlap):
        chunk = ' '.join(words[i:i+size])
        if len(chunk) > 100:
            chunks.append(chunk)
    return chunks

chunks = split_chunks(raw_text)


def generate_questions(chunk, quiz_id):
    prompt = f"""You are an expert quiz generator. Based on the text below, generate exactly 3 questions in JSON format:
- 1 type "vrai_faux"
- 1 type "choix_multiple" (4 choices)
- 1 type "texte"

TEXT: {chunk[:1000]}

Respond ONLY with valid JSON (no explanation, no markdown):
{{
  "questions": [
    {{"texte":"...","niveau":"facile","indice":"...","quiz_id":{quiz_id},"type":"vrai_faux","choix_a":null,"choix_b":null,"choix_c":null,"choix_d":null,"bonne_reponse_choix":null,"bonne_reponse_bool":true,"reponse_attendue":null}},
    {{"texte":"...","niveau":"moyen","indice":"...","quiz_id":{quiz_id},"type":"choix_multiple","choix_a":"...","choix_b":"...","choix_c":"...","choix_d":"...","bonne_reponse_choix":"a","bonne_reponse_bool":null,"reponse_attendue":null}},
    {{"texte":"...","niveau":"difficile","indice":"...","quiz_id":{quiz_id},"type":"texte","choix_a":null,"choix_b":null,"choix_c":null,"choix_d":null,"bonne_reponse_choix":null,"bonne_reponse_bool":null,"reponse_attendue":"..."}}
  ]
}}"""

    try:
        # ✅ Ollama local — بدل HuggingFace
        response = requests.post(
            "http://localhost:11434/api/chat",
            json={
                "model": "gemma:2b",
                "messages": [{"role": "user", "content": prompt}],
                "stream": False
            },
            timeout=60
        )

        raw = response.json()["message"]["content"]

        # نظّف الـ JSON من markdown
        raw = re.sub(r'```json|```', '', raw).strip()

        match = re.search(r'\{.*\}', raw, re.DOTALL)
        if match:
            try:
                return json.loads(match.group())
            except json.JSONDecodeError:
                return None
        return None

    except Exception as e:
        print(f"Erreur chunk: {e}")
        return None

# ── المعالجة ──
all_questions = []
for i, chunk in enumerate(chunks):
    print(f"Traitement du chunk {i+1}/{len(chunks)}...")
    result = generate_questions(chunk, quiz_id)
    if result and "questions" in result:
        all_questions.extend(result["questions"])

# ── حفظ النتائج ──
with open(output_path, "w", encoding="utf-8") as f:
    json.dump({"questions": all_questions}, f, ensure_ascii=False, indent=2)

# ✅ بدون emoji — لا مشكلة Unicode
print(f"Genere {len(all_questions)} questions avec succes")