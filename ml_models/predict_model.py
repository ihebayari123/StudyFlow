#!/usr/bin/env python3
# ml_models/predict_model.py
import sys
import json
import joblib
import numpy as np
import pandas as pd  # ← AJOUTÉ
import os

def predict(features_json):
    """
    Reçoit les features en JSON et retourne la prédiction
    """
    try:
        # Charger le modèle
        model_path = os.path.join(os.path.dirname(__file__), 'account_risk_model.pkl')
        model = joblib.load(model_path)
        
        # Parser les features
        features = json.loads(features_json)
        
        # Vérifier le format
        if not isinstance(features, list):
            return json.dumps({'error': 'Features must be a list'})
        
        # Prédiction avec noms de features (plus de warning)
        feature_names = ['login_frequency', 'failed_login_attempts', 'time_since_last_login',
                        'hour_of_login', 'account_age_days', 'role_encoded', 'is_weekend']
        X = pd.DataFrame([features], columns=feature_names)
        
        probability = float(model.predict_proba(X)[0][1])
        prediction = int(model.predict(X)[0])
        
        # Niveau de risque
        if probability > 0.7:
            level = 'HIGH'
        elif probability > 0.3:
            level = 'MEDIUM'
        else:
            level = 'LOW'
        
        result = {
            'probability': probability,
            'prediction': prediction,
            'level': level,
            'success': True
        }
        
        return json.dumps(result)
        
    except Exception as e:
        return json.dumps({'error': str(e), 'success': False})

if __name__ == '__main__':
    if len(sys.argv) != 2:
        print(json.dumps({'error': 'Usage: python predict_model.py "[features]"'}))
        sys.exit(1)
    
    print(predict(sys.argv[1]))