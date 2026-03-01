# ai/generate.py

import sys
import json
import re
import fitz
from huggingface_hub import InferenceClient


pdf_path = sys.argv[1]
output_path = sys.argv[2]
quiz_id = int(sys.argv[3])

  



client = InferenceClient(token=HF_TOKEN)


doc = fitz.open(pdf_path)
raw_text = ""
for page in doc:
    raw_text += page.get_text()

# Chunking
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

Respond ONLY with valid JSON:
{{
  "questions": [
    {{"texte":"...","niveau":"facile","indice":"...","quiz_id":{quiz_id},"type":"vrai_faux","choix_a":null,"choix_b":null,"choix_c":null,"choix_d":null,"bonne_reponse_choix":null,"bonne_reponse_bool":true,"reponse_attendue":null}},
    {{"texte":"...","niveau":"facile","indice":"...","quiz_id":{quiz_id},"type":"choix_multiple","choix_a":"...","choix_b":"...","choix_c":"...","choix_d":"...","bonne_reponse_choix":"a","bonne_reponse_bool":null,"reponse_attendue":null}},
    {{"texte":"...","niveau":"facile","indice":"...","quiz_id":{quiz_id},"type":"texte","choix_a":null,"choix_b":null,"choix_c":null,"choix_d":null,"bonne_reponse_choix":null,"bonne_reponse_bool":null,"reponse_attendue":"..."}}
  ]
}}"""

    response = client.chat.completions.create(
        model="meta-llama/Llama-3.2-3B-Instruct",
        messages=[{"role": "user", "content": prompt}],
        max_tokens=1500,
        temperature=0.7
    )

    raw = response.choices[0].message.content
    match = re.search(r'\{.*\}', raw, re.DOTALL)
    if match:
        try:
            return json.loads(match.group())
        except:
            return None
    return None

all_questions = []
for chunk in chunks:
    result = generate_questions(chunk, quiz_id)
    if result and "questions" in result:
        all_questions.extend(result["questions"])


with open(output_path, "w", encoding="utf-8") as f:
    json.dump({"questions": all_questions}, f, ensure_ascii=False)

print(f"Generated {len(all_questions)} questions")