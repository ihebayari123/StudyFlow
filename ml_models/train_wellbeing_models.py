#!/usr/bin/env python3
"""
Script d'entraînement pour les modèles de bien-être:
- best_happiness_model.pkl: prédit le bonheur basé sur les heures de sommeil et d'étude
- best_stress_model.keras: prédit le stress basé sur les 5 paramètres
"""

import numpy as np
import pickle
import os
import sys

# Ajouter le chemin parent pour les imports
sys.path.insert(0, os.path.dirname(__file__))

# Chemins des modèles
MODELS_DIR = os.path.dirname(__file__)
HAPPINESS_MODEL_PATH = os.path.join(MODELS_DIR, 'best_happiness_model.pkl')
STRESS_MODEL_PATH = os.path.join(MODELS_DIR, 'best_stress_model.keras')

def generate_happiness_data(n_samples=1000):
    """Génère des données d'entraînement pour le bonheur"""
    np.random.seed(42)
    
    # Features: [sleep_hours, study_hours]
    sleep_hours = np.random.uniform(0, 12, n_samples)
    study_hours = np.random.uniform(0, 16, n_samples)
    
    # Target: happiness (0-1)
    # Hypothèses:
    # - Bonheur optimal avec 7-9h de sommeil
    # - Bonheur diminue avec trop d'étude (>10h)
    # - Relation en forme de cloche pour le sommeil
    happiness = (
        0.3 +  # baseline
        0.4 * np.exp(-((sleep_hours - 7.5) ** 2) / 8) +  # sommeil optimal à 7.5h
        0.3 * (1 - np.exp(-study_hours / 5)) -  # étude jusqu'à 5h augmente le bonheur
        0.2 * np.exp(-((study_hours - 10) ** 2) / 20)  # trop d'étude diminue le bonheur
    )
    
    # Ajouter du bruit
    happiness += np.random.normal(0, 0.05, n_samples)
    happiness = np.clip(happiness, 0, 1)
    
    X = np.column_stack([sleep_hours, study_hours])
    y = happiness
    
    return X, y

def generate_stress_data(n_samples=1000):
    """Génère des données d'entraînement pour le stress"""
    np.random.seed(42)
    
    # Features: [sleep_hours, study_hours, coffee_cups, age, sport_hours]
    sleep_hours = np.random.uniform(0, 12, n_samples)
    study_hours = np.random.uniform(0, 16, n_samples)
    coffee_cups = np.random.uniform(0, 10, n_samples)
    age = np.random.uniform(18, 65, n_samples)
    sport_hours = np.random.uniform(0, 10, n_samples)
    
    # Target: stress (0-1)
    # Hypothèses:
    # - Stress augmente avec moins de sommeil
    # - Stress augmente avec plus d'étude
    # - Stress augmente avec plus de café
    # - Stress diminue avec le sport
    # - Stress varie avec l'âge (plus élevée aux extrêmes)
    stress = (
        0.5 -  # baseline
        0.3 * (sleep_hours / 12) +  # moins de sommeil = plus de stress
        0.2 * (study_hours / 16) +  # plus d'étude = plus de stress
        0.15 * (coffee_cups / 10) +  # plus de café = plus de stress
        0.1 * np.abs(age - 30) / 35 +  # âge extrême = plus de stress
        0.15 * np.exp(-sport_hours / 3)  # moins de sport = plus de stress
    )
    
    # Ajouter du bruit
    stress += np.random.normal(0, 0.05, n_samples)
    stress = np.clip(stress, 0, 1)
    
    X = np.column_stack([sleep_hours, study_hours, coffee_cups, age, sport_hours])
    y = stress
    
    return X, y

def train_happiness_model():
    """Entraîne le modèle de bonheur"""
    print("Génération des données d'entraînement pour le bonheur...")
    X, y = generate_happiness_data(1000)
    
    # Utiliser RandomForest pour le modèle de bonheur
    from sklearn.ensemble import RandomForestRegressor
    from sklearn.model_selection import train_test_split
    from sklearn.metrics import mean_squared_error, r2_score
    
    X_train, X_test, y_train, y_test = train_test_split(X, y, test_size=0.2, random_state=42)
    
    print("Entraînement du modèle de bonheur...")
    model = RandomForestRegressor(n_estimators=100, random_state=42, n_jobs=-1)
    model.fit(X_train, y_train)
    
    # Évaluer le modèle
    y_pred = model.predict(X_test)
    mse = mean_squared_error(y_test, y_pred)
    r2 = r2_score(y_test, y_pred)
    
    print(f"  MSE: {mse:.4f}")
    print(f"  R2: {r2:.4f}")
    
    # Sauvegarder le modèle
    with open(HAPPINESS_MODEL_PATH, 'wb') as f:
        pickle.dump(model, f)
    
    print(f"Modèle bonheur sauvegardé: {HAPPINESS_MODEL_PATH}")
    return model

def train_stress_model():
    """Entraînement du modèle de stress avec Keras"""
    print("\nGénération des données d'entraînement pour le stress...")
    X, y = generate_stress_data(1000)
    
    from sklearn.model_selection import train_test_split
    from sklearn.preprocessing import StandardScaler
    
    X_train, X_test, y_train, y_test = train_test_split(X, y, test_size=0.2, random_state=42)
    
    # Normaliser les features
    scaler = StandardScaler()
    X_train_scaled = scaler.fit_transform(X_train)
    X_test_scaled = scaler.transform(X_test)
    
    # Sauvegarder le scaler pour une utilisation ultérieure
    SCALER_PATH = os.path.join(MODELS_DIR, 'stress_scaler.pkl')
    with open(SCALER_PATH, 'wb') as f:
        pickle.dump(scaler, f)
    
    print("Entraînement du modèle de stress avec Keras...")
    
    # Créer le modèle Keras
    try:
        import tensorflow as tf
        from tensorflow import keras
        from tensorflow.keras import layers
    except ImportError:
        print("TensorFlow n'est pas installé. Installation...")
        os.system(f"{sys.executable} -m pip install tensorflow")
        import tensorflow as tf
        from tensorflow import keras
        from tensorflow.keras import layers
    
    model = keras.Sequential([
        layers.Dense(64, activation='relu', input_shape=(5,)),
        layers.Dropout(0.2),
        layers.Dense(32, activation='relu'),
        layers.Dropout(0.2),
        layers.Dense(16, activation='relu'),
        layers.Dense(1, activation='sigmoid')
    ])
    
    model.compile(
        optimizer=keras.optimizers.Adam(learning_rate=0.001),
        loss='binary_crossentropy',
        metrics=['mae']
    )
    
    # Entraîner le modèle
    history = model.fit(
        X_train_scaled, y_train,
        epochs=100,
        batch_size=32,
        validation_split=0.2,
        verbose=0
    )
    
    # Évaluer le modèle
    loss, mae = model.evaluate(X_test_scaled, y_test, verbose=0)
    print(f"  Loss: {loss:.4f}")
    print(f"  MAE: {mae:.4f}")
    
    # Sauvegarder le modèle
    model.save(STRESS_MODEL_PATH)
    print(f"Modèle stress sauvegardé: {STRESS_MODEL_PATH}")
    
    return model

def main():
    print("=" * 50)
    print("Entraînement des modèles de bien-être")
    print("=" * 50)
    
    # Entraîner le modèle de bonheur
    train_happiness_model()
    
    # Entraîner le modèle de stress
    train_stress_model()
    
    print("\n" + "=" * 50)
    print("Entraînement terminé avec succès!")
    print("=" * 50)
    print(f"\nModèles créés:")
    print(f"  - Bonheur: {HAPPINESS_MODEL_PATH}")
    print(f"  - Stress: {STRESS_MODEL_PATH}")

if __name__ == "__main__":
    main()
