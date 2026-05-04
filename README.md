# 🤖 GREENBITE - Système ChatBot Intelligent

## ✨ Système Complet et Fonctionnel

Votre chatbot est maintenant équipé d'un **système intelligent de compréhension logique** avec accès complet au catalogue de produits pour faire des recommandations réalistes.

---

## 🎯 Fonctionnalités du ChatBot

### 1. **Analyse Intelligente des Messages**

Le chatbot comprend automatiquement ce que demande l'utilisateur:

- **Mots-clés de recherche**: fruits, légumes, viande, produits bio, etc.
- **Critères diététiques**: vegan, végétarien, sans gluten, sans lactose, bio
- **Contraintes budgétaires**: moins de 5€, prix max, bon marché
- **Focus nutritionnel**: protéines, calories, santé, digestion
- **Allergies alimentaires**: détecte automatiquement les allergies

### 2. **Intentions Détectées**

| Intent | Description | Exemple |
|--------|-------------|---------|
| `product_search` | Recherche de produits | "Je cherche des fruits bio" |
| `diet_preference` | Préférences diététiques | "Je suis vegan" |
| `budget_search` | Recherche par budget | "Moins de 5 euros" |
| `health_nutrition` | Recommandations nutritionnelles | "Riche en protéines" |
| `allergies` | Allergies alimentaires | "Je suis allergique aux arachides" |
| `help_request` | Demande d'aide générale | "Aide-moi!" |

### 3. **Accès au Catalogue de Produits**

Le chatbot interroge la base de données en temps réel:

```
Table: produits (avec jointure avis)
├── Recherche par mots-clés (nom, catégorie, marque)
├── Filtrage par budget (prix min/max)
├── Filtrage diététique (bio, vegan, etc.)
├── Filtrage nutritionnel (protéines, calories)
└── Évaluations réelles (notes + nombre d'avis)
```

### 4. **Recommandations Réalistes**

Chaque produit recommandé inclut:

- ✓ Nom et marque
- ✓ Prix actuel
- ✓ Catégorie
- ✓ Note moyenne (basée sur les avis validés)
- ✓ Nombre d'avis clients
- ✓ Contenu nutritionnel (calories, protéines)
- ✓ Raison contextuelle de la recommandation

### 5. **Réponses Contextuelles**

Le bot génère des réponses naturelles basées sur:
- L'intention détectée
- Les mots-clés
- Le contexte utilisateur

---

## 📊 Exemples d'Utilisation

### Exemple 1: Recherche Simple
```
Utilisateur: "Je cherche des fruits bio"
↓
Intent: diet_preference (confiance: 90%)
Mots-clés: fruits, bio
Régime: bio
↓
Bot: "Très bien! 💚 Je vais vous proposer nos meilleurs produits bio."
↓
Recommandations: 
  - Pommes Bio 1kg (4.50€) ⭐⭐⭐⭐ 8 avis
  - Bananes Bio 1kg (3.50€) ⭐⭐⭐⭐ 12 avis
```

### Exemple 2: Recherche Budgétaire
```
Utilisateur: "Des produits vegan pas chers"
↓
Intent: diet_preference + budget (confiance: 90%)
Régime: vegan
Budget max: 5€
↓
Bot: "Parfait! 👌 J'ai trouvé exactement ce qu'il faut pour vous."
↓
Recommandations:
  - Tropico (1.50€) 💰 Au meilleur prix
  - Yaourt Nature Bio 500g (6.50€) 🌱 Adapté à votre régime
```

### Exemple 3: Recherche Nutritionnelle
```
Utilisateur: "Je cherche quelque chose riche en protéines"
↓
Intent: health_nutrition (confiance: 85%)
Focus: protéines
↓
Bot: "Excellent! 💪 Voici mes recommandations santé."
↓
Recommandations:
  - Fromage Blanc 400g (8.50€) 💪 Riche en protéines (12g)
  - Yaourt Grec 500g (7.50€) 💪 Riche en protéines (15g)
```

### Exemple 4: Détection d'Allergie
```
Utilisateur: "Je suis allergique aux arachides"
↓
Intent: allergies (confiance: 95%)
↓
Bot: "Sécurité avant tout! ✓ Je cherche les produits sans allergènes."
↓
Recommandations:
  - Produits vérifiés sans arachides
  - Allergènes: AUCUN
```

---

## 🏗️ Architecture Technique

### Structure des Fichiers

```
Controller/
└── ChatBotController.php
    ├── handleMessage()      # Traitement des messages
    ├── getConversation()    # Récupération historique
    └── startNewConversation() # Nouvelle conversation

Model/
└── ChatBot.php
    ├── Analyse Intelligente
    │   ├── analyzeMessage()
    │   ├── extractSearchKeywords()
    │   ├── extractDietCriteria()
    │   ├── extractBudgetCriteria()
    │   └── extractNutritionFocus()
    │
    ├── Recommandations
    │   ├── getRecommendations()
    │   ├── generateRecommendationReason()
    │   └── getDietCategoryName()
    │
    └── Gestion Conversation
        ├── createConversation()
        ├── addMessage()
        ├── detectSentiment()
        └── generateResponse()

api/chatbot/
├── message.php         # POST endpoint pour les messages
├── conversation.php    # GET endpoint pour l'historique
├── start.php          # Démarrer une conversation
└── suggestions.php    # Suggestions rapides
```

### Base de Données

Tables utilisées:
- `produits` - Catalogue complet
- `avis` - Évaluations clients (note, titre, texte, statut)
- `chat_conversations` - Sessions de chat
- `chat_messages` - Messages utilisateur/bot

Query de recommandations:
```sql
SELECT p.*, 
       AVG(a.note) as note_moyenne,
       COUNT(a.id_avis) as nombre_avis
FROM produits p
LEFT JOIN avis a ON p.id_produit = a.id_produit AND a.statut = "approuve"
WHERE p.statut = "actif" 
  AND (conditions dynamiques basées sur l'analyse)
GROUP BY p.id_produit
ORDER BY note_moyenne DESC, nombre_avis DESC
LIMIT 5
```

---

## 🔌 API Endpoints

### POST /api/chatbot/message.php

**Request:**
```json
{
  "id_conversation": 1,
  "id_utilisateur": 1,
  "message": "Je cherche des produits bio pas chers"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Message traité",
  "id_conversation": 1,
  "id_message": 52,
  "bot_response": "Très bien! 💚 Je vais vous proposer nos meilleurs produits bio.",
  "analysis": {
    "intent": "diet_preference",
    "confiance": 0.90,
    "has_search_keywords": true,
    "has_diet_criteria": true,
    "has_budget_criteria": true,
    "search_keywords": ["bio"],
    "diet_type": "bio",
    "found_products_count": 5
  },
  "recommendations": [
    {
      "id_produit": 1,
      "nom": "Poudre Amande Bio 200g",
      "prix": 13.50,
      "marque": "Nature&Vie",
      "categorie": "Bio",
      "image": "...",
      "note_moyenne": 4.7,
      "nombre_avis": 15,
      "calories": 570,
      "proteines": 21,
      "confiance": 0.75,
      "raison": "🔍 Correspond à votre recherche • 🌱 Adapté à votre régime • ⭐ Très bien noté (4.7/5)"
    },
    ...
  ]
}
```

---

## 📈 Statut du Système

✅ **Complètement Fonctionnel**

- ✓ Analyse logique des messages
- ✓ Extraction de mots-clés intelligent
- ✓ Détection de régimes alimentaires
- ✓ Extraction de contraintes budgétaires
- ✓ Focus nutritionnel détecté
- ✓ Détection d'allergies
- ✓ Accès au catalogue en temps réel
- ✓ Jointure produits + avis
- ✓ Recommandations filtrées et triées
- ✓ Réponses contextuelles
- ✓ Historique des conversations
- ✓ Analyse de sentiment

---

## 🚀 Démarrage

### Installation

1. Assurez-vous que la base de données `greenbite` est disponible
2. Les tables suivantes doivent exister:
   - `produits`
   - `avis`
   - `chat_conversations`
   - `chat_messages`

### Utilisation

Envoyez un message POST à `/api/chatbot/message.php`:

```javascript
fetch('/api/chatbot/message.php', {
  method: 'POST',
  headers: { 'Content-Type': 'application/json' },
  body: JSON.stringify({
    id_conversation: 1,
    id_utilisateur: 1,
    message: "Je cherche des produits bio"
  })
})
.then(r => r.json())
.then(data => console.log(data.recommendations));
```

---

## 🔮 Améliorations Possibles

- [ ] Intégration avec API NLP avancée (Google NLP, AWS Comprehend)
- [ ] Machine Learning pour apprentissage des préférences
- [ ] Recommandations personnalisées par utilisateur
- [ ] Support multichannel (SMS, Email, WhatsApp)
- [ ] Analytics avancées
- [ ] Support multilingue

---

## 📝 Notes Importantes

1. **Évaluations**: Seules les avis avec `statut = "approuve"` sont comptabilisées
2. **Recommandations**: Retourne maximum 5 produits par défaut
3. **Budget**: Reconnaît les formats "moins de 5€", "5 euros max", "bon marché"
4. **Diététique**: Cherche en catégorie du produit
5. **Sentiment**: Analysé et enregistré pour chaque message

---

## 📞 Support

Pour toute question sur le système de chatbot:
1. Consultez ce README
2. Vérifiez la structure des tables en base de données
3. Vérifiez les logs PHP (error_log)

---

**GreenBite ChatBot v2.0 - 2026 | Intelligence Artificielle ✨**
