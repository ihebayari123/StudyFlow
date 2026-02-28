#!/usr/bin/env python3
import os
import sys
import json
import pandas as pd
import numpy as np
from sklearn.feature_extraction.text import TfidfVectorizer
from sklearn.preprocessing import LabelEncoder
from sklearn.ensemble import RandomForestRegressor
from sklearn.model_selection import train_test_split
from sklearn.metrics import mean_absolute_error, r2_score
import joblib

# Créer les répertoires nécessaires
os.makedirs('ml_data', exist_ok=True)
os.makedirs('ml_models', exist_ok=True)

# Générer des données d'entraînement si le fichier CSV n'existe pas
csv_path = 'ml_data/produits.csv'

if not os.path.exists(csv_path):
    print("Génération des données d'entraînement...")
    
    data = {
        'nom': [
            'iPhone 15 Pro Max',
            'Samsung Galaxy S24',
            'iPad Air',
            'Google Pixel 8',
            'OnePlus 12',
            'Laptop Dell XPS 13',
            'MacBook Air M2',
            'Chaise de Bureau Gamer',
            'Bureau Blanc Moderne',
            'Lampe LED RGB',
            'Clavier Mécanique',
            'Souris Gaming',
            'Écran 27 pouces 4K',
            'Casque Audio Sony',
            'Power Bank 20000mAh',
            'Câble USB-C',
            'Housse de Téléphone',
            'Verre Trempé Protecteur',
            'Station de Charge',
            'Adaptateur HDMI',
            'T-Shirt Coton Premium',
            'Pantalon Jeans Bleu',
            'Chemise Professionnelle',
            'Robe de Soirée',
            'Veste Cuir',
            'Chaussures Compétition',
            'Baskets Sport',
            'Sandales Confort',
            'Python Programming',
            'Data Science Handbook',
            'Web Development Guide',
            'Machine Learning Bible',
            'The Pragmatic Programmer',
            'Clean Code'
        ],
        'description': [
            'Smartphone haut de gamme avec écran OLED 6.7 pouces, processeur A17 Pro, caméra triple 48MP, batterie 4000mAh',
            'Téléphone Android flagship avec écran Dynamic AMOLED 120Hz, Galaxy AI intégré, caméra révolutionnaire',
            'Tablette polyvalente avec écran Liquid Retina 11 pouces, processeur M1, parfait pour études et travail',
            'Pixel 8 avec IA Google Tensor, caméra computationnelle exceptionnelle, écran OLED vibrant',
            'Smartphone chinois performant avec Snapdragon 8 Gen 3 L, charge ultra-rapide 100W',
            'Ultrabook professionnel léger avec processeur Intel Core i7, 16GB RAM, SSD 512GB',
            'Laptop premium Apple avec processeur M2 8-core, 16GB mémoire unifié, batterie 18h',
            'Chaise ergonomique pour gaming avec support lombaire ajustable, revêtement cuir synthétique',
            'Bureau blanc style scandinave dimensions 120x60cm, plateau MDF massif, pieds métal stable',
            'Lampe de bureau LED RGB avec USB, 3 modes de couleur, 10 niveaux luminosité',
            'Clavier mécanique RGB switch Blue, 104 touches, anti-ghosting, câble tressé',
            'Souris gaming sans fil avec DPI ajustable, 8 boutons programmables, batterie 20h',
            'Moniteur LED 27 pouces 4K UHD 60Hz, IPS panel, USB-C intégré, cadre ultra-fin',
            'Casque audio Bluetooth Sony WH-1000XM5 réduction bruit active, son premium, 30h batterie',
            'Power bank 20000mAh avec charge rapide 65W, 2 ports USB-C, affichage LED numérique',
            'Câble USB-C vers USB-C certifié 100W, 2 mètres, charge et données rapides',
            'Housse silicone premium Samsung Galaxy S24, protection 4 coins renforcée, design ergonomique',
            'Verre trempé protecteur iPhone 15 Pro, dureté 9H, pose facile avec cadre alignement',
            'Station de charge sans fil Qi 15W, compatible tous téléphones, design minimaliste',
            'Adaptateur HDMI 2.1 8K, longueur 1m, doré haute qualité pour écrans professionnels',
            'T-shirt 100% coton premium pour homme, couleur blanc cassé, taille M à XXL disponible',
            'Pantalon jeans bleu classique coupe slim, denim durable, fermeture éclair qualité',
            'Chemise de bureau propres rayures blanches bleu, coton respirant, repassage facile',
            'Robe de soirée noire élégante avec dentelle, découpe sensuelle, parfait mariage',
            'Veste cuir noir véritable pour femme, doublure viscose, col classique rebrasé',
            'Chaussures de compétition running Nike carbone, semelle réactive, poids léger 180g',
            'Baskets sport blanc cassé polyvalentes, confort quotidien, soutien arch optimisé',
            'Sandales confort orthopédiques, semelle mémoire mousse, parfait climat chaud',
            'Guide complet Python 3 programmation, 800 pages, exercices pratiques inclus',
            'Handbook data science avec ML, statistiques, notebooks Jupyter, 900 pages',
            'Guide web development HTML CSS JavaScript, responsive design, avec CMS modernes',
            'Bible machine learning approfondie, neural networks, deep learning, recommandations',
            'Classic programmation agile pragmatique, refactoring, patterns, conseil professionnel',
            'Code épuré principes clean, nommage, fonctions, gestion erreurs, architecture'
        ],
        'categorie': [
            'Électronique', 'Électronique', 'Électronique', 'Électronique', 'Électronique',
            'Informatique', 'Informatique', 'Mobilier', 'Mobilier', 'Accessoires',
            'Accessoires', 'Accessoires', 'Informatique', 'Électronique', 'Électronique',
            'Accessoires', 'Accessoires', 'Accessoires', 'Accessoires', 'Accessoires',
            'Vêtements', 'Vêtements', 'Vêtements', 'Vêtements', 'Vêtements',
            'Chaussures', 'Chaussures', 'Chaussures', 'Livres', 'Livres',
            'Livres', 'Livres', 'Livres', 'Livres'
        ],
        'prix': [
            1299, 999, 649, 799, 749,
            1599, 1799, 399, 249, 89,
            159, 79, 549, 399, 59,
            29, 19, 9, 39, 19,
            49, 39, 79, 199, 449,
            149, 89, 49, 49, 59,
            59, 45, 69, 89
        ]
    }
    
    df = pd.DataFrame(data)
    df.to_csv(csv_path, index=False)
    print(f"Données créées: {csv_path}")
else:
    print(f"Lecture du fichier {csv_path}...")
    df = pd.read_csv(csv_path)

print(f"Nombre de produits: {len(df)}")
print(f"Catégories: {df['categorie'].unique()}")

# Préparation des données
X_text = df['nom'] + ' ' + df['description']
y = df['prix'].values

# TF-IDF sur nom + description
# Ne pas utiliser 'french' (non supporté par scikit-learn par défaut) — utiliser None
tfidf = TfidfVectorizer(max_features=100, stop_words=None, ngram_range=(1, 2))
X_tfidf = tfidf.fit_transform(X_text)

# LabelEncoder sur catégorie
le = LabelEncoder()
X_categorie = le.fit_transform(df['categorie']).reshape(-1, 1)

# Combiner les features (TF-IDF sparse + catégorie)
from scipy.sparse import hstack
X_combined = hstack([X_tfidf, X_categorie])

# Split train/test (80/20)
X_train, X_test, y_train, y_test = train_test_split(
    X_combined, y, test_size=0.2, random_state=42
)

# Entraîner RandomForestRegressor
print("\nEntraînement du modèle...")
model = RandomForestRegressor(n_estimators=100, max_depth=15, random_state=42, n_jobs=-1)
model.fit(X_train, y_train)

# Évaluation
y_pred_train = model.predict(X_train)
y_pred_test = model.predict(X_test)
mae_train = mean_absolute_error(y_train, y_pred_train)
mae_test = mean_absolute_error(y_test, y_pred_test)
r2_train = r2_score(y_train, y_pred_train)
r2_test = r2_score(y_test, y_pred_test)

print(f"MAE Train: {mae_train:.2f}")
print(f"MAE Test: {mae_test:.2f}")
print(f"R² Train: {r2_train:.4f}")
print(f"R² Test: {r2_test:.4f}")

# Statistiques de prix
prix_stats = {
    'min': float(df['prix'].min()),
    'max': float(df['prix'].max()),
    'mean': float(df['prix'].mean()),
    'std': float(df['prix'].std())
}

# Sauvegarder les modèles
joblib.dump(model, 'ml_models/model_prix.pkl')
joblib.dump(tfidf, 'ml_models/vectorizer.pkl')
joblib.dump(le, 'ml_models/label_encoder.pkl')

# Métadonnées
metadata = {
    'categories': list(df['categorie'].unique()),
    'prix_stats': prix_stats,
    'mae_test': float(mae_test),
    'r2_test': float(r2_test),
    'n_samples': len(df),
    'tfidf_features': 100,
    'model_type': 'RandomForestRegressor'
}

with open('ml_models/metadata.json', 'w') as f:
    json.dump(metadata, f, indent=2)

print("\n✅ Modèle et métadonnées sauvegardés:")
print("   - ml_models/model_prix.pkl")
print("   - ml_models/vectorizer.pkl")
print("   - ml_models/label_encoder.pkl")
print("   - ml_models/metadata.json")
