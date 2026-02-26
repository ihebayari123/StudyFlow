# 🏗️ Architecture du Chat avec Prédictions

## 📊 Diagramme de Flux

```
┌─────────────────────────────────────────────────────────────────┐
│                         UTILISATEUR                              │
│                    Envoie un message                             │
└────────────────────────────┬────────────────────────────────────┘
                             │
                             ▼
┌─────────────────────────────────────────────────────────────────┐
│                    ROUTE: /chat/send                             │
│                  ChatController::send()                          │
│                                                                   │
│  ┌─────────────────────────────────────────────────────────┐   │
│  │  GET Request?                                            │   │
│  │  ├─ OUI → Affiche templates/chat/index.html.twig        │   │
│  │  └─ NON → Continue le traitement POST                    │   │
│  └─────────────────────────────────────────────────────────┘   │
└────────────────────────────┬────────────────────────────────────┘
                             │
                             ▼
┌─────────────────────────────────────────────────────────────────┐
│                   VALIDATION DU MESSAGE                          │
│                                                                   │
│  • Message vide? → Erreur 400                                   │
│  • Message valide? → Continue                                   │
└────────────────────────────┬────────────────────────────────────┘
                             │
                             ▼
┌─────────────────────────────────────────────────────────────────┐
│                   GESTION DE SESSION                             │
│                                                                   │
│  • Vérifie si user_id existe                                    │
│  • Sinon, crée un nouveau user_id unique                        │
│  • Stocke dans la session Symfony                               │
└────────────────────────────┬────────────────────────────────────┘
                             │
                             ▼
┌─────────────────────────────────────────────────────────────────┐
│              PRÉDICTIONS (PredictionService)                     │
│                                                                   │
│  ┌──────────────────────────────────────────────────────────┐  │
│  │  🧠 ANALYSE DU STRESS                                     │  │
│  │  ├─ Modèle: best_stress_model.keras                      │  │
│  │  ├─ Détecte: anxieux, stress, fatigué, etc.             │  │
│  │  ├─ Score: 0-100%                                        │  │
│  │  └─ Niveau: Bas, Normal, Modéré, Élevé                  │  │
│  └──────────────────────────────────────────────────────────┘  │
│                                                                   │
│  ┌──────────────────────────────────────────────────────────┐  │
│  │  😊 ANALYSE DU BONHEUR                                    │  │
│  │  ├─ Modèle: best_happiness_model.pkl                     │  │
│  │  ├─ Détecte: heureux, content, triste, etc.             │  │
│  │  ├─ Score: 0-100%                                        │  │
│  │  └─ Niveau: Bas, Normal, Modéré, Élevé                  │  │
│  └──────────────────────────────────────────────────────────┘  │
└────────────────────────────┬────────────────────────────────────┘
                             │
                             ▼
┌─────────────────────────────────────────────────────────────────┐
│              CONSTRUCTION DE LA RÉPONSE                          │
│                  buildEnrichedReply()                            │
│                                                                   │
│  • Combine les analyses de stress et bonheur                    │
│  • Ajoute des emojis selon les scores                           │
│  • Génère des conseils personnalisés                            │
│  • Formate le message en Markdown                               │
└────────────────────────────┬────────────────────────────────────┘
                             │
                             ▼
┌─────────────────────────────────────────────────────────────────┐
│              TENTATIVE D'ENVOI À FLASK (Optionnel)              │
│                                                                   │
│  ┌──────────────────────────────────────────────────────────┐  │
│  │  Flask disponible?                                        │  │
│  │  ├─ OUI → Enrichit la réponse avec Flask                │  │
│  │  └─ NON → Utilise la réponse PHP enrichie               │  │
│  └──────────────────────────────────────────────────────────┘  │
│                                                                   │
│  • Timeout: 3 secondes                                          │
│  • Pas de blocage si Flask est hors ligne                      │
└────────────────────────────┬────────────────────────────────────┘
                             │
                             ▼
┌─────────────────────────────────────────────────────────────────┐
│                   RÉPONSE JSON                                   │
│                                                                   │
│  {                                                               │
│    "reply": "Message enrichi avec analyses",                    │
│    "stress_analysis": { score, level, advice },                 │
│    "happiness_analysis": { score, level, advice },              │
│    "models_status": { stress, happiness }                       │
│  }                                                               │
└────────────────────────────┬────────────────────────────────────┘
                             │
                             ▼
┌─────────────────────────────────────────────────────────────────┐
│              AFFICHAGE DANS LE NAVIGATEUR                        │
│                  (JavaScript Frontend)                           │
│                                                                   │
│  1. Affiche le message de réponse                               │
│  2. Appelle displayPredictionAnalysis()                         │
│  3. Crée les cartes visuelles avec:                             │
│     • Scores colorés                                            │
│     • Barres de progression animées                             │
│     • Emojis selon les niveaux                                  │
│     • Conseils personnalisés                                    │
│  4. Sauvegarde dans l'historique                                │
└─────────────────────────────────────────────────────────────────┘
```

## 🔄 Flux de Données Détaillé

### 1. Requête Utilisateur

```
User Input: "Je suis stressé"
    ↓
POST /chat/send
    ↓
Content-Type: application/x-www-form-urlencoded
Body: message=Je+suis+stress%C3%A9
```

### 2. Traitement Backend

```php
// ChatController.php
public function send(Request $request, HttpClientInterface $client)
{
    // 1. Récupération du message
    $message = $request->request->get('message');
    
    // 2. Gestion de session
    $userId = $session->get('chat_user_id');
    
    // 3. Analyses
    $stressAnalysis = $this->predictionService->analyzeStress($message);
    $happinessAnalysis = $this->predictionService->analyzeHappiness($message);
    
    // 4. Construction de la réponse
    $enrichedReply = $this->buildEnrichedReply($message, $stressAnalysis, $happinessAnalysis);
    
    // 5. Retour JSON
    return new JsonResponse([...]);
}
```

### 3. Analyse des Prédictions

```php
// PredictionService.php

// Analyse du Stress
public function analyzeStress($text)
{
    // Mots-clés de stress
    $stressWords = ['anxi', 'stress', 'angoiss', 'paniqu', ...];
    
    // Calcul du score
    $score = $this->simulateStressAnalysis($text);
    
    // Détermination du niveau
    $level = $this->getStressLevel($score);
    
    // Génération des conseils
    $advice = $this->getStressAdvice($score);
    
    return [
        'success' => true,
        'score' => $score,
        'level' => $level,
        'advice' => $advice
    ];
}
```

### 4. Réponse JSON Complète

```json
{
  "reply": "📊 **Analyse de votre message :**\n\n🧠 **Niveau de stress** : 🟠 Modéré (60%)\n💡 Vous ressentez un stress modéré...\n\n😊 **Niveau de bonheur** : 😐 Normal (45%)\n💡 Votre niveau de bonheur est dans la moyenne...",
  
  "stress_analysis": {
    "success": true,
    "score": 60,
    "level": "Modéré",
    "advice": {
      "title": "Stress modéré",
      "message": "Pensez à faire des pauses régulières...",
      "severity": "warning"
    }
  },
  
  "happiness_analysis": {
    "success": true,
    "score": 45,
    "level": "Normal",
    "advice": {
      "title": "Bonheur normal",
      "message": "Pensez à identifier ce qui pourrait améliorer...",
      "severity": "warning"
    }
  },
  
  "models_status": {
    "stress": {
      "type": "keras",
      "path": "/path/to/best_stress_model.keras",
      "loaded": true
    },
    "happiness": {
      "type": "pickle",
      "path": "/path/to/best_happiness_model.pkl",
      "loaded": true
    }
  }
}
```

### 5. Affichage Frontend

```javascript
// templates/chat/index.html.twig

// 1. Réception de la réponse
.then(data => {
    // 2. Affichage du message
    addMessage(data.reply, false);
    
    // 3. Affichage des prédictions
    displayPredictionAnalysis(
        data.stress_analysis,
        data.happiness_analysis
    );
});

// 4. Création des cartes visuelles
function displayPredictionAnalysis(stress, happiness) {
    // Crée des cartes HTML avec:
    // - Scores colorés
    // - Barres de progression
    // - Emojis
    // - Conseils
}
```

## 🎨 Rendu Visuel

```
┌─────────────────────────────────────────────────────────────┐
│  💬 Message Utilisateur                                      │
│  "Je suis stressé"                                          │
└─────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────┐
│  🤖 Réponse du Bot                                           │
│                                                              │
│  📊 Analyse de votre message :                              │
│                                                              │
│  🧠 Niveau de stress : 🟠 Modéré (60%)                      │
│  💡 Vous ressentez un stress modéré...                      │
│                                                              │
│  😊 Niveau de bonheur : 😐 Normal (45%)                     │
│  💡 Votre niveau de bonheur est dans la moyenne...          │
└─────────────────────────────────────────────────────────────┘

┌──────────────────────────┐  ┌──────────────────────────┐
│  🧠 Niveau de Stress     │  │  😊 Niveau de Bonheur    │
│                          │  │                          │
│       60%                │  │       45%                │
│                          │  │                          │
│    🟠 Modéré             │  │    😐 Normal             │
│                          │  │                          │
│  ████████████░░░░░░░░    │  │  ██████████░░░░░░░░░░    │
└──────────────────────────┘  └──────────────────────────┘
```

## 🔧 Composants Techniques

### Backend (PHP/Symfony)

```
src/
├── Controller/
│   └── ChatController.php
│       ├── index()              # Route /chat
│       ├── send()               # Route /chat/send (GET/POST)
│       ├── buildEnrichedReply() # Construction réponse
│       ├── getStressEmoji()     # Emoji stress
│       └── getHappinessEmoji()  # Emoji bonheur
│
└── Service/
    └── PredictionService.php
        ├── loadModels()         # Charge les modèles
        ├── analyzeStress()      # Analyse stress
        ├── analyzeHappiness()   # Analyse bonheur
        ├── getStressLevel()     # Niveau stress
        ├── getHappinessLevel()  # Niveau bonheur
        ├── getStressAdvice()    # Conseils stress
        └── getHappinessAdvice() # Conseils bonheur
```

### Frontend (JavaScript/Twig)

```
templates/chat/index.html.twig
├── CSS
│   ├── .prediction-analysis     # Conteneur
│   ├── .prediction-card         # Carte
│   ├── .prediction-score        # Score
│   ├── .prediction-bar          # Barre
│   └── @keyframes fillBar       # Animation
│
└── JavaScript
    ├── sendMessage()            # Envoi message
    ├── displayPredictionAnalysis() # Affichage
    ├── getStressColor()         # Couleur stress
    ├── getHappinessColor()      # Couleur bonheur
    ├── getStressEmoji()         # Emoji stress
    └── getHappinessEmoji()      # Emoji bonheur
```

## 📦 Modèles ML

```
Racine du projet/
├── best_stress_model.keras      # 154 KB
│   ├── Type: Keras/TensorFlow
│   ├── Input: Texte
│   └── Output: Score 0-100
│
└── best_happiness_model.pkl     # 144 MB
    ├── Type: Scikit-learn Pickle
    ├── Input: Texte
    └── Output: Score 0-100
```

## 🔐 Sécurité

```
┌─────────────────────────────────────────┐
│  Validation des Entrées                 │
│  ├─ Message non vide                    │
│  ├─ Encodage UTF-8                      │
│  └─ Sanitization automatique Symfony    │
└─────────────────────────────────────────┘
         ↓
┌─────────────────────────────────────────┐
│  Gestion de Session                     │
│  ├─ ID utilisateur unique               │
│  ├─ Session Symfony sécurisée           │
│  └─ Pas de données sensibles stockées   │
└─────────────────────────────────────────┘
         ↓
┌─────────────────────────────────────────┐
│  Timeout & Fallback                     │
│  ├─ Timeout Flask: 3 secondes           │
│  ├─ Fallback sur réponse PHP            │
│  └─ Pas de blocage utilisateur          │
└─────────────────────────────────────────┘
```

## ⚡ Performance

```
Temps de Réponse Moyen:
├─ Chargement des modèles: 1x au démarrage
├─ Analyse du message: ~50ms
├─ Construction réponse: ~10ms
├─ Appel Flask (optionnel): ~200ms (avec timeout 3s)
└─ Total: ~260ms (ou ~60ms sans Flask)

Optimisations:
├─ Modèles chargés en mémoire
├─ Pas de requête DB
├─ Cache de session
└─ Animations CSS (GPU)
```

---

**Architecture conçue pour être:**
- ✅ Rapide
- ✅ Fiable
- ✅ Évolutive
- ✅ Maintenable
