#!/usr/bin/env python3
import sys
import json
import os
import joblib
from sklearn.feature_extraction.text import TfidfVectorizer
from scipy.sparse import hstack

# Vérifier les arguments
if len(sys.argv) != 4:
    json.dump({"success": False, "error": "Invalid arguments"}, sys.stdout)
    sys.exit(1)

nom = sys.argv[1]
description = sys.argv[2]
categorie = sys.argv[3]

# Chemin des modèles (ml_models dans le répertoire du projet)
script_dir = os.path.dirname(os.path.abspath(__file__))
models_dir = os.path.join(script_dir, 'ml_models')

# Fichiers modèles
model_path = os.path.join(models_dir, 'model_prix.pkl')
vectorizer_path = os.path.join(models_dir, 'vectorizer.pkl')
label_encoder_path = os.path.join(models_dir, 'label_encoder.pkl')
metadata_path = os.path.join(models_dir, 'metadata.json')

# Vérifier que les fichiers existent
if not all(os.path.exists(p) for p in [model_path, vectorizer_path, label_encoder_path, metadata_path]):
    json.dump({"success": False, "error": "Model files not found"}, sys.stdout)
    sys.exit(1)

# Charger les modèles
model = joblib.load(model_path)
vectorizer = joblib.load(vectorizer_path)
label_encoder = joblib.load(label_encoder_path)

with open(metadata_path, 'r') as f:
    metadata = json.load(f)

# Transformer le texte
X_text = nom + ' ' + description
X_tfidf = vectorizer.transform([X_text])

# Transformer la catégorie
try:
    # Vérifier que la catégorie existe
    categorie_idx = list(label_encoder.classes_).index(categorie)
    X_categorie = label_encoder.transform([categorie]).reshape(-1, 1)
except ValueError:
    json.dump({"success": False, "error": f"Category '{categorie}' not found"}, sys.stdout)
    sys.exit(1)

# Combiner les features
X_combined = hstack([X_tfidf, X_categorie])

# Prédire
prix_predit = float(model.predict(X_combined)[0])

# Déterminer la confiance basée sur R² et prix_stats
r2_test = metadata['r2_test']
prix_stats = metadata['prix_stats']

# Vérifier si le prix est dans une plage raisonnable
prix_range = prix_stats['max'] - prix_stats['min']
prix_deviation = abs(prix_predit - prix_stats['mean']) / prix_stats['std'] if prix_stats['std'] > 0 else 0

if r2_test > 0.7 and prix_deviation < 2:
    confidence = "high"
elif r2_test > 0.5 or prix_deviation < 3:
    confidence = "medium"
else:
    confidence = "low"

# Afficher UNIQUEMENT le JSON
json.dump({
    "success": True,
    "prix_predit": round(prix_predit, 2),
    "confidence": confidence
}, sys.stdout)
