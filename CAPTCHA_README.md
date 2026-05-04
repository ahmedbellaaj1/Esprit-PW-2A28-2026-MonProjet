# Documentation CAPTCHA

## Aperçu

Un système CAPTCHA complet a été ajouté à la page d'inscription pour renforcer la sécurité et prévenir les attaques automatisées.

## Fonctionnalités

✅ **Image CAPTCHA aléatoire**
- Génère une image PNG sécurisée avec lettres majuscules, minuscules et chiffres
- Bruits et distorsions pour prévenir la reconnaissance optique
- Mise à jour aléatoire du code à chaque rafraîchissement

✅ **Audio CAPTCHA**
- Lecture audio du code CAPTCHA
- Fichier WAV généré dynamiquement
- Sons distincts pour chaque caractère

✅ **Boutons de contrôle**
- Bouton "Rafraîchir" pour générer un nouveau CAPTCHA
- Bouton "Son" pour écouter le code audio

✅ **Validation serveur**
- Vérification du code saisi contre le code en session
- Validation insensible à la casse
- Message d'erreur personnalisé en cas d'erreur

## Fichiers modifiés/créés

### Nouveaux fichiers
- **Controller/captcha.php** - Génère l'image PNG du CAPTCHA
- **Controller/captcha-sound.php** - Génère le son WAV du CAPTCHA

### Fichiers modifiés
- **View/auth.php** 
  - Ajout du formulaire CAPTCHA dans le formulaire d'enregistrement
  - Ajout des boutons de contrôle (rafraîchir, son)
  - Initialisation du CAPTCHA au chargement de la page
  - JavaScript pour gérer l'interaction utilisateur

- **Controller/auth.php**
  - Validation du CAPTCHA lors de l'enregistrement
  - Message d'erreur approprié

- **View/style.css**
  - Styles pour l'image CAPTCHA
  - Styles pour les boutons de contrôle
  - Responsive design pour les appareils mobiles

## Utilisation

### Pour l'utilisateur
1. Remplir le formulaire d'enregistrement
2. Voir l'image CAPTCHA avec des caractères aléatoires
3. Cliquer sur l'icône haut-parleur pour entendre le code (optionnel)
4. Cliquer sur l'icône de rafraîchissement pour générer un nouveau CAPTCHA
5. Saisir le code CAPTCHA dans le champ prévu
6. Soumettre le formulaire

### Pour les développeurs

#### Structure de session
```php
$_SESSION['captcha_code'] // Contient le code CAPTCHA actuel
```

#### Points d'intégration
- **captcha.php** : Point de terminaison pour l'image CAPTCHA
- **captcha-sound.php** : Point de terminaison pour le son CAPTCHA
- **auth.php** (Controller) : Validation du CAPTCHA

## Sécurité

- ✅ Code unique généré aléatoirement
- ✅ Bruits et distorsions visuels
- ✅ Validation serveur robuste
- ✅ Régénération automatique après tentative échouée
- ✅ Cache désactivé pour éviter les rejeux

## Compatibilité

- ✅ PHP 7.4+
- ✅ Tous les navigateurs modernes
- ✅ Appareils mobiles (design responsive)
- ✅ Accessibilité avec audio

## Configuration

Aucune configuration requise. Le CAPTCHA fonctionne immédiatement après installation.

## Dépendances

- PHP GD Library (pour la génération d'images)
- PHP Standard Library (pour la génération de son WAV)

## Troubleshooting

### Le CAPTCHA n'affiche pas l'image
- Vérifiez que la GD Library est activée : `extension=gd`
- Vérifiez les permissions des fichiers

### Le son ne joue pas
- Vérifiez que le navigateur autorise l'audio
- Testez avec un navigateur différent

### La validation échoue toujours
- Vérifiez que la session est correctement configurée
- Assurez-vous que les cookies de session sont activés
- La saisie est insensible à la casse (accepte majuscules et minuscules)

