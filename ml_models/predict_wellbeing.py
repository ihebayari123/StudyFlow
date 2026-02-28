#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Script de prédiction pour le chatbot StudyFlow
Utilise deux modèles ML:
- best_happiness_model.pkl: prédit le bonheur basé sur les heures de sommeil et d'étude
- best_stress_model.keras: prédit le stress basé sur les 5 paramètres
"""

import sys
import json
import os
import numpy as np
import io
import codecs

# Force UTF-8 pour stdin/stdout
sys.stdin = io.TextIOWrapper(sys.stdin.buffer, encoding='utf-8')
sys.stdout = io.TextIOWrapper(sys.stdout.buffer, encoding='utf-8')
sys.stderr = io.TextIOWrapper(sys.stderr.buffer, encoding='utf-8')

# Supprimer les messages TensorFlow
os.environ['TF_CPP_MIN_LOG_LEVEL'] = '3'
os.environ['TF_ENABLE_ONEDNN_OPTS'] = '0'

# Désactiver les barres de progression Keras
os.environ['TF_CPP_MIN_LOG_LEVEL'] = '3'

# Chemins des modèles
MODELS_DIR = os.path.join(os.path.dirname(__file__))
HAPPINESS_MODEL_PATH = os.path.join(MODELS_DIR, 'best_happiness_model.pkl')
STRESS_MODEL_PATH = os.path.join(MODELS_DIR, 'best_stress_model.keras')
SCALER_PATH = os.path.join(MODELS_DIR, 'stress_scaler.pkl')

# Variables globales pour les modèles chargés
_models = None
_scaler = None

def load_models():
    """Charge les modèles ML"""
    global _models, _scaler
    
    _models = {
        'happiness': None,
        'stress': None
    }
    
    try:
        # Charger le modèle de bonheur (scikit-learn .pkl)
        if os.path.exists(HAPPINESS_MODEL_PATH):
            import pickle
            with open(HAPPINESS_MODEL_PATH, 'rb') as f:
                _models['happiness'] = pickle.load(f)
        else:
            pass  # Suppress logging for clean JSON output
    except Exception as e:
        pass  # Suppress logging for clean JSON output
    
    try:
        # Charger le modèle de stress (Keras .keras)
        if os.path.exists(STRESS_MODEL_PATH):
            import tensorflow as tf
            _models['stress'] = tf.keras.models.load_model(STRESS_MODEL_PATH)
        else:
            pass  # Suppress logging for clean JSON output
    except Exception as e:
        pass  # Suppress logging for clean JSON output
    
    try:
        # Charger le scaler pour le modèle de stress
        if os.path.exists(SCALER_PATH):
            import pickle
            with open(SCALER_PATH, 'rb') as f:
                _scaler = pickle.load(f)
        else:
            pass  # Suppress logging for clean JSON output
    except Exception as e:
        pass  # Suppress logging for clean JSON output
    
    return _models

def predict_happiness(sleep_hours, study_hours):
    """Prédit le niveau de bonheur basé sur sommeil et étude"""
    global _models
    
    try:
        if _models['happiness'] is None:
            return None, "Modèle bonheur non chargé"
        
        # Préparer les features [sommeil, étude]
        features = np.array([[sleep_hours, study_hours]])
        
        # Faire la prédiction
        prediction = _models['happiness'].predict(features)
        
        # Extraire la valeur de prédiction
        if hasattr(prediction, 'flatten'):
            pred_value = float(prediction.flatten()[0])
        else:
            pred_value = float(prediction[0])
        
        return pred_value, None
    except Exception as e:
        return None, str(e)

def predict_stress(sleep_hours, study_hours, coffee_cups, age, sport_hours=0):
    """Prédit le niveau de stress basé sur les 5 paramètres"""
    global _models, _scaler
    
    try:
        if _models['stress'] is None:
            return None, "Modèle stress non chargé"
        
        if _scaler is None:
            return None, "Scaler non chargé"
        
        # Préparer les features [sommeil, étude, café, age, sport]
        # L'ordre doit correspondre à l'entraînement du modèle
        features = np.array([[sleep_hours, study_hours, coffee_cups, age, sport_hours]])
        
        # Normaliser les features
        features_scaled = _scaler.transform(features)
        
        # Faire la prédiction (verbose=0 pour éviter les messages Keras)
        prediction = _models['stress'].predict(features_scaled, verbose=0)
        
        # Extraire la valeur de prédiction
        if hasattr(prediction, 'flatten'):
            pred_value = float(prediction.flatten()[0])
        else:
            pred_value = float(prediction[0])
        
        return pred_value, None
    except Exception as e:
        return None, str(e)

def main():
    """Point d'entrée principal"""
    # Charger les modèles au démarrage
    load_models()
    
    # Lire les données depuis stdin ou arguments
    sleep_hours = 7
    study_hours = 4
    coffee_cups = 2
    age = 20
    sport_hours = 0
    
    # Essayer de lire depuis stdin d'abord
    try:
        if not sys.stdin.isatty():
            input_data = sys.stdin.read()
            if input_data:
                data = json.loads(input_data)
                sleep_hours = float(data.get('sleep_hours', 7))
                study_hours = float(data.get('study_hours', 4))
                coffee_cups = int(data.get('coffee_cups', 2))
                age = int(data.get('age', 20))
                sport_hours = float(data.get('sport_hours', 0))
    except:
        pass
    
    # Sinon, lire depuis les arguments de ligne de commande
    import argparse
    parser = argparse.ArgumentParser()
    parser.add_argument('--sleep_hours', type=float, default=7)
    parser.add_argument('--study_hours', type=float, default=4)
    parser.add_argument('--coffee_cups', type=int, default=2)
    parser.add_argument('--age', type=int, default=20)
    parser.add_argument('--sport_hours', type=float, default=0)
    args, _ = parser.parse_known_args()
    
    sleep_hours = args.sleep_hours
    study_hours = args.study_hours
    coffee_cups = args.coffee_cups
    age = args.age
    sport_hours = args.sport_hours
    
    # Validation des entrées
    if sleep_hours < 0 or sleep_hours > 24:
        print(json.dumps({"error": "Heures de sommeil invalides (doivent être entre 0 et 24)"}))
        sys.exit(1)
    
    if study_hours < 0 or study_hours > 24:
        print(json.dumps({"error": "Heures d'étude invalides (doivent être entre 0 et 24)"}))
        sys.exit(1)
    
    if coffee_cups < 0 or coffee_cups > 50:
        print(json.dumps({"error": "Nombre de cafés invalide"}))
        sys.exit(1)
    
    if age < 1 or age > 120:
        print(json.dumps({"error": "Âge invalide"}))
        sys.exit(1)
    
    # Faire les prédictions
    results = {
        "input": {
            "sleep_hours": sleep_hours,
            "study_hours": study_hours,
            "coffee_cups": coffee_cups,
            "age": age,
            "sport_hours": sport_hours
        },
        "predictions": {}
    }
    
    # Prédiction bonheur (utilise seulement sommeil et étude)
    happiness_pred, happiness_error = predict_happiness(sleep_hours, study_hours)
    if happiness_error:
        results["predictions"]["happiness"] = {
            "error": happiness_error,
            "value": None
        }
    else:
        # Normaliser le résultat si nécessaire (0-1 ou 1-10)
        happiness_normalized = max(0, min(10, happiness_pred * 10))  # Assumer 0-1
        level = "Eleve" if happiness_normalized >= 7 else ("Moyen" if happiness_normalized >= 4 else "Bas")
        results["predictions"]["happiness"] = {
            "value": happiness_pred,
            "normalized": happiness_normalized,
            "level": level
        }
    
    # Prédiction stress (utilise les 5 paramètres)
    stress_pred, stress_error = predict_stress(sleep_hours, study_hours, coffee_cups, age, sport_hours)
    if stress_error:
        results["predictions"]["stress"] = {
            "error": stress_error,
            "value": None
        }
    else:
        # Normaliser le résultat (0-1 vers 0-100%)
        stress_percentage = max(0, min(100, stress_pred * 100))
        level = "Eleve" if stress_percentage >= 70 else ("Moyen" if stress_percentage >= 40 else "Bas")
        results["predictions"]["stress"] = {
            "value": stress_pred,
            "percentage": stress_percentage,
            "level": level
        }
    
    # Résultats finaux
    results["success"] = True
    results["message"] = "Predictions effectuees avec succes"
    
    # Envoyer le résultat en JSON
    print(json.dumps(results, ensure_ascii=False))

if __name__ == "__main__":
    main()
