# 🤖 Chat avec Prédictions - Documentation

## 📋 Résumé des Modifications

J'ai modifié le système de chat pour que la route `/chat/send` ouvre l'interface du chat et affiche les prédictions des modèles de stress et de bonheur en temps réel.

## 🎯 Fonctionnalités Implémentées

### 1. Route `/chat/send` Améliorée

La route [`/chat/send`](src/Controller/ChatController.php:30) supporte maintenant **GET** et **POST**:

- **GET** `/chat/send` : Ouvre l'interface du chat
- **POST** `/chat/send` : Traite les messages et retourne les prédictions

### 2. Prédictions en Temps Réel

Chaque message envoyé est analysé par deux modèles:

#### 🧠 Modèle de Stress (`best_stress_model.keras`)
- Analyse le niveau de stress dans le message
- Retourne un score de 0-100%
- Niveaux: Bas, Normal, Modéré, Élevé
- Couleurs: 🟢 Vert, 🟡 Jaune, 🟠 Orange, 🔴 Rouge

#### 😊 Modèle de Bonheur (`best_happiness_model.pkl`)
- Analyse le niveau de bonheur dans le message
- Retourne un score de 0-100%
- Niveaux: Bas, Normal, Modéré, Élevé
- Emojis: 😔 Triste, 😐 Neutre, 🙂 Content, 😄 Heureux

### 3. Interface Visuelle Enrichie

Les prédictions sont affichées avec:
- **Cartes visuelles** avec scores et niveaux
- **Barres de progression animées**
- **Codes couleur** selon les niveaux
- **Conseils personnalisés** basés sur l'analyse

## 🔧 Fichiers Modifiés

### 1. [`src/Controller/ChatController.php`](src/Controller/ChatController.php)

**Modifications principales:**

```php
#[Route('/chat/send', name: 'chat_send', methods: ['GET', 'POST'])]
public function send(Request $request, HttpClientInterface $client)
{
    // GET: Affiche l'interface du chat
    if ($request->isMethod('GET')) {
        return $this->render('chat/index.html.twig', [
            'modelsStatus' => $this->predictionService->getModelsStatus()
        ]);
    }
    
    // POST: Traite le message et retourne les prédictions
    $message = $request->request->get('message');
    $stressAnalysis = $this->predictionService->analyzeStress($message);
    $happinessAnalysis = $this->predictionService->analyzeHappiness($message);
    
    // Retourne une réponse enrichie avec les analyses
    return new JsonResponse([
        'reply' => $enrichedReply,
        'stress_analysis' => $stressAnalysis,
        'happiness_analysis' => $happinessAnalysis,
        'models_status' => $this->predictionService->getModelsStatus()
    ]);
}
```

**Nouvelles méthodes:**
- `buildEnrichedReply()` : Construit une réponse avec les prédictions
- `getStressEmoji()` : Retourne l'emoji selon le niveau de stress
- `getHappinessEmoji()` : Retourne l'emoji selon le niveau de bonheur

### 2. [`templates/chat/index.html.twig`](templates/chat/index.html.twig)

**Ajouts JavaScript:**

```javascript
// Affiche les prédictions visuellement
function displayPredictionAnalysis(stressAnalysis, happinessAnalysis) {
    // Crée des cartes visuelles avec scores, niveaux et barres de progression
}

// Fonctions helper pour les couleurs et emojis
function getStressColor(score) { /* ... */ }
function getHappinessColor(score) { /* ... */ }
function getStressEmoji(score) { /* ... */ }
function getHappinessEmoji(score) { /* ... */ }
```

**Ajouts CSS:**

```css
.prediction-analysis { /* Conteneur des prédictions */ }
.prediction-card { /* Carte individuelle */ }
.prediction-score { /* Score en grand */ }
.prediction-bar { /* Barre de progression animée */ }
```

## 🚀 Utilisation

### Méthode 1: Accès Direct

Visitez: `http://localhost:8000/chat/send`

Cela ouvrira directement l'interface du chat.

### Méthode 2: Via le Chat Normal

1. Visitez: `http://localhost:8000/chat`
2. Tapez un message
3. Les prédictions s'affichent automatiquement

### Méthode 3: Test avec la Page de Test

Visitez: `http://localhost:8000/test_chat.html`

Cette page permet de tester:
- Messages avec stress élevé
- Messages positifs
- Messages neutres
- Ouverture directe du chat

## 📊 Exemples de Réponses

### Exemple 1: Message Stressé

**Message:** "Je suis très stressé et anxieux"

**Réponse JSON:**
```json
{
  "reply": "📊 **Analyse de votre message :**\n\n🧠 **Niveau de stress** : 🔴 Élevé (80%)\n💡 Votre niveau de stress est très élevé...\n\n😊 **Niveau de bonheur** : 😔 Bas (15%)\n💡 Votre niveau de bonheur est faible...",
  "stress_analysis": {
    "success": true,
    "score": 80,
    "level": "Élevé",
    "advice": {
      "title": "Niveau de stress élevé détecté",
      "message": "Votre niveau de stress est très élevé...",
      "severity": "danger"
    }
  },
  "happiness_analysis": {
    "success": true,
    "score": 15,
    "level": "Bas",
    "advice": {
      "title": "Bonheur faible",
      "message": "Votre niveau de bonheur est faible...",
      "severity": "danger"
    }
  },
  "models_status": {
    "stress": {
      "type": "keras",
      "loaded": true
    },
    "happiness": {
      "type": "pickle",
      "loaded": true
    }
  }
}
```

### Exemple 2: Message Positif

**Message:** "Je suis heureux et content"

**Affichage:**
- 🟢 Stress: Bas (10%)
- 😄 Bonheur: Élevé (85%)

## 🔍 Vérification des Modèles

Les modèles sont chargés automatiquement au démarrage:

```bash
# Vérifier que les fichiers existent
ls -la best_stress_model.keras
ls -la best_happiness_model.pkl
```

**Statut des modèles:**
- ✅ `best_stress_model.keras` : Modèle Keras pour le stress
- ✅ `best_happiness_model.pkl` : Modèle Pickle pour le bonheur

## 🎨 Personnalisation

### Modifier les Seuils de Score

Dans [`src/Service/PredictionService.php`](src/Service/PredictionService.php):

```php
private function getStressLevel($score)
{
    if ($score >= 80) return 'Élevé';    // Modifier ici
    if ($score >= 60) return 'Modéré';   // Modifier ici
    if ($score >= 40) return 'Normal';   // Modifier ici
    return 'Bas';
}
```

### Modifier les Couleurs

Dans [`templates/chat/index.html.twig`](templates/chat/index.html.twig):

```javascript
function getStressColor(score) {
    if (score >= 80) return '#ef4444'; // Rouge
    if (score >= 60) return '#f97316'; // Orange
    if (score >= 40) return '#eab308'; // Jaune
    return '#22c55e'; // Vert
}
```

## 🐛 Dépannage

### Problème: Les modèles ne se chargent pas

**Solution:**
1. Vérifiez que les fichiers existent:
   - `best_stress_model.keras`
   - `best_happiness_model.pkl`
2. Vérifiez les permissions des fichiers
3. Consultez les logs Symfony

### Problème: L'API Flask est hors ligne

**Solution:**
Le système fonctionne même sans Flask! Les prédictions sont générées par PHP.

Si Flask est disponible, il enrichit les réponses, sinon le système utilise uniquement les prédictions PHP.

### Problème: Les prédictions ne s'affichent pas

**Solution:**
1. Ouvrez la console du navigateur (F12)
2. Vérifiez les erreurs JavaScript
3. Vérifiez que la réponse JSON contient `stress_analysis` et `happiness_analysis`

## 📝 Notes Techniques

### Architecture

```
User Message
    ↓
ChatController::send()
    ↓
PredictionService::analyzeStress()
PredictionService::analyzeHappiness()
    ↓
buildEnrichedReply()
    ↓
JSON Response
    ↓
JavaScript displayPredictionAnalysis()
    ↓
Visual Cards in Chat
```

### Sécurité

- ✅ Validation des messages (non vides)
- ✅ Gestion des sessions utilisateur
- ✅ Timeout pour l'API Flask (3 secondes)
- ✅ Gestion des erreurs avec fallback

### Performance

- ⚡ Prédictions en temps réel
- ⚡ Animations CSS optimisées
- ⚡ Pas de dépendance externe obligatoire
- ⚡ Cache des modèles en mémoire

## 🎯 Prochaines Étapes

1. **Améliorer les modèles** : Entraîner avec plus de données
2. **Ajouter plus d'émotions** : Colère, peur, surprise, etc.
3. **Historique des prédictions** : Graphiques d'évolution
4. **Notifications** : Alertes si stress trop élevé
5. **Export PDF** : Rapport d'analyse complet

## 📞 Support

Pour toute question ou problème:
1. Consultez les logs Symfony: `var/log/dev.log`
2. Vérifiez la console du navigateur
3. Testez avec la page de test: `/test_chat.html`

---

**Version:** 1.0.0  
**Date:** 2026-02-26  
**Auteur:** Kilo Code AI Assistant
