# 📊 Fonctionnalité Questionnaire - Évaluation du Stress et de la Joie

## Vue d'ensemble

Le système de chat a été amélioré avec une fonctionnalité de questionnaire interactif qui permet d'évaluer le niveau de stress et de joie des utilisateurs en fonction de plusieurs facteurs de leur vie quotidienne.

## Fonctionnalités

### 1. Déclenchement du Questionnaire

L'utilisateur peut démarrer le questionnaire de plusieurs façons :
- En cliquant sur le bouton "📊 Questionnaire" dans les réponses rapides
- En tapant des mots-clés comme : `questionnaire`, `évaluation`, `test`, `analyse`

### 2. Questions Posées (5 questions)

Le questionnaire collecte les informations suivantes :

1. **Heures de sommeil** (par nuit)
   - Format : nombre décimal (ex: 7 ou 7.5)
   - Plage valide : 0-24 heures

2. **Heures d'étude** (par jour)
   - Format : nombre décimal (ex: 4 ou 5.5)
   - Plage valide : 0-24 heures

3. **Minutes de sport** (par jour)
   - Format : nombre entier (ex: 30 ou 60)
   - Plage valide : 0-1440 minutes

4. **Tasses de café** (par jour)
   - Format : nombre entier (ex: 2 ou 3)
   - Plage valide : 0-20 tasses

5. **Âge**
   - Format : nombre entier (ex: 25)
   - Plage valide : 1-120 ans

### 3. Validation des Réponses

Chaque réponse est validée en temps réel :
- Vérification du format (nombre)
- Vérification de la plage valide
- Messages d'erreur clairs en cas de saisie invalide
- Possibilité de corriger la réponse

### 4. Calcul du Niveau de Stress

Le score de stress (0-100%) est calculé en fonction de :

#### Facteurs de Stress (Total : 100 points max)

**Sommeil (0-30 points)** - Moins de sommeil = plus de stress
- < 5h : +30 points
- 5-6h : +25 points
- 6-7h : +15 points
- 7-8h : +5 points
- ≥ 8h : 0 points

**Heures d'étude (0-25 points)** - Trop d'étude = plus de stress
- > 10h : +25 points
- 8-10h : +20 points
- 6-8h : +15 points
- 4-6h : +10 points
- 2-4h : +5 points
- < 2h : 0 points

**Sport (0-20 points)** - Moins de sport = plus de stress
- < 15 min : +20 points
- 15-30 min : +15 points
- 30-45 min : +10 points
- 45-60 min : +5 points
- ≥ 60 min : 0 points

**Café (0-15 points)** - Trop de café = plus de stress
- ≥ 5 tasses : +15 points
- 4 tasses : +12 points
- 3 tasses : +8 points
- 2 tasses : +4 points
- 0-1 tasse : 0 points

**Âge (0-10 points)** - Facteur d'âge
- < 20 ans : +10 points
- 20-25 ans : +8 points
- 25-30 ans : +5 points
- 30-50 ans : 0 points
- > 50 ans : +5 points

#### Niveaux de Stress
- **0-39%** : 🟢 Bas
- **40-59%** : 🟡 Normal
- **60-79%** : 🟠 Modéré
- **80-100%** : 🔴 Élevé

### 5. Calcul du Niveau de Joie

Le score de joie (0-100%) est calculé en fonction de :

#### Facteurs de Joie (Score de base : 50 points)

**Sommeil (±35 points)** - Bon sommeil = plus de joie
- 7-9h (optimal) : +35 points
- 6-7h : +25 points
- 5-6h : +10 points
- < 5h : -20 points
- > 10h : +10 points

**Heures d'étude (±15 points)** - Étude équilibrée = plus de joie
- 2-4h (optimal) : +15 points
- 4-6h : +10 points
- 6-8h : +5 points
- > 8h : -10 points
- 0-2h : +5 points

#### Niveaux de Joie
- **0-39%** : 😔 Bas
- **40-59%** : 😐 Normal
- **60-79%** : 🙂 Modéré
- **80-100%** : 😄 Élevé

### 6. Affichage des Résultats

Après avoir répondu aux 5 questions, l'utilisateur reçoit :

1. **Récapitulatif des réponses**
   - Toutes les données saisies

2. **Analyse du stress**
   - Score en pourcentage
   - Niveau (Bas/Normal/Modéré/Élevé)
   - Emoji visuel
   - Conseils personnalisés

3. **Analyse de la joie**
   - Score en pourcentage
   - Niveau (Bas/Normal/Modéré/Élevé)
   - Emoji visuel
   - Conseils personnalisés

4. **Visualisation graphique**
   - Cartes colorées avec barres de progression
   - Couleurs adaptées au niveau (vert/jaune/orange/rouge)

## Architecture Technique

### Fichiers Modifiés

1. **`src/Service/PredictionService.php`**
   - Ajout de `calculateStressFromQuestionnaire()`
   - Ajout de `calculateJoyFromQuestionnaire()`

2. **`src/Controller/ChatController.php`**
   - Ajout de `handleQuestionnaireResponse()`
   - Ajout de `getQuestionnaireQuestion()`
   - Ajout de `validateQuestionnaireAnswer()`
   - Ajout de `buildQuestionnaireResults()`
   - Gestion du flux de questionnaire via session

3. **`templates/chat/index.html.twig`**
   - Ajout du bouton "📊 Questionnaire" dans les réponses rapides
   - Mise à jour du message de bienvenue

### Gestion de Session

Le questionnaire utilise la session Symfony pour :
- `questionnaire_active` : Indique si un questionnaire est en cours
- `questionnaire_step` : Étape actuelle (1-5)
- `questionnaire_data` : Données collectées

### Flux de Données

```
Utilisateur → ChatController → Session
                ↓
         Validation des réponses
                ↓
         PredictionService
                ↓
    Calcul des scores (stress & joie)
                ↓
         Affichage des résultats
```

## Utilisation

### Pour l'utilisateur

1. Cliquez sur "📊 Questionnaire" ou tapez "questionnaire"
2. Répondez aux 5 questions une par une
3. Consultez vos résultats avec les scores et conseils
4. Refaites le test quand vous voulez en tapant "questionnaire"

### Pour le développeur

#### Modifier les questions
Éditez la méthode `getQuestionnaireQuestion()` dans [`ChatController.php`](src/Controller/ChatController.php)

#### Modifier les calculs
Éditez les méthodes dans [`PredictionService.php`](src/Service/PredictionService.php) :
- `calculateStressFromQuestionnaire()` pour le stress
- `calculateJoyFromQuestionnaire()` pour la joie

#### Ajouter des questions
1. Augmentez le nombre total d'étapes dans `handleQuestionnaireResponse()`
2. Ajoutez la question dans `getQuestionnaireQuestion()`
3. Ajoutez la validation dans `validateQuestionnaireAnswer()`
4. Mettez à jour les calculs dans `PredictionService`

## Exemples de Réponses

### Exemple 1 : Étudiant stressé
```
Sommeil : 5 heures
Étude : 9 heures
Sport : 15 minutes
Café : 4 tasses
Âge : 22 ans

Résultat :
- Stress : 78% (Modéré) 🟠
- Joie : 35% (Bas) 😔
```

### Exemple 2 : Personne équilibrée
```
Sommeil : 8 heures
Étude : 3 heures
Sport : 60 minutes
Café : 1 tasse
Âge : 28 ans

Résultat :
- Stress : 15% (Bas) 🟢
- Joie : 100% (Élevé) 😄
```

## Conseils Personnalisés

Le système fournit des conseils adaptés au niveau détecté :

### Stress Élevé (≥80%)
"Votre niveau de stress est très élevé. Il est important de prendre des mesures pour vous détendre. Essayez des techniques de respiration profonde, la méditation, ou parlez à un professionnel de santé."

### Stress Modéré (60-79%)
"Vous ressentez un stress modéré. Pensez à faire des pauses régulières, à pratiquer une activité physique, et à maintenir une bonne hygiène de vie."

### Joie Faible (<40%)
"Votre niveau de bonheur est faible. Il pourrait être bénéfique de parler à un professionnel de santé ou à un proche de confiance. Pensez aussi à pratiquer des activités qui vous apportent du plaisir."

## Améliorations Futures

- [ ] Sauvegarder l'historique des questionnaires
- [ ] Graphiques d'évolution dans le temps
- [ ] Comparaison avec les moyennes
- [ ] Recommandations personnalisées basées sur l'IA
- [ ] Export des résultats en PDF
- [ ] Intégration avec les modèles ML existants
- [ ] Questions conditionnelles basées sur les réponses précédentes
- [ ] Support multilingue

## Notes Importantes

- Les calculs sont basés sur des algorithmes simples et ne remplacent pas un avis médical professionnel
- Les données sont stockées uniquement en session et ne sont pas persistées en base de données
- Le questionnaire peut être refait autant de fois que souhaité
- Les réponses sont validées pour éviter les erreurs de saisie
