# Guide d'utilisation du CAPTCHA

## Pour les utilisateurs finaux

### Lors de l'inscription

1. **Remplissez les champs du formulaire** :
   - Nom
   - Prénom
   - Email
   - Mot de passe
   - Photo (optionnel)
   - Rôle
   - Statut

2. **Complétez le CAPTCHA de sécurité** :
   - Vous verrez une image contenant 6 caractères aléatoires (lettres majuscules, minuscules et chiffres)
   - Lisez les caractères de l'image
   - Saisissez-les exactement dans le champ texte

3. **Utilisez les boutons d'aide** :
   - 🔄 **Bouton Rafraîchir** : Génère un nouveau CAPTCHA si celui-ci est trop difficile à lire
   - 🔊 **Bouton Son** : Écoute les caractères du CAPTCHA (utile pour les personnes malvoyantes)

4. **Soumettez le formulaire** en cliquant sur "Créer le compte"

### Conseils

- Le code CAPTCHA est **insensible à la casse** (A = a)
- Si vous vous trompez, un message d'erreur vous le signalera
- Vous pourrez générer un nouveau CAPTCHA et réessayer
- Le son du CAPTCHA peut aider si la lecture est difficile

---

## Pour les administrateurs/développeurs

### Fichiers du CAPTCHA

| Fichier | Description |
|---------|-------------|
| `Controller/captcha.php` | Génère l'image PNG du CAPTCHA |
| `Controller/captcha-sound.php` | Génère le fichier audio WAV du CAPTCHA |
| `Controller/captcha-config.php` | Configuration du CAPTCHA |
| `View/auth.php` | Formulaire d'inscription avec CAPTCHA |

### Points d'intégration

**Dans le formulaire HTML** :
```html
<img id="captcha-image" src="../Controller/captcha.php" alt="CAPTCHA">
<button id="captcha-refresh">Rafraîchir</button>
<button id="captcha-audio-btn">Son</button>
<input name="captcha" type="text" required>
```

**En PHP (validation)** :
```php
$captcha = strtoupper($_POST['captcha'] ?? '');

if ($captcha === '' || $_SESSION['captcha_code'] !== $captcha) {
    // Erreur CAPTCHA
}
```

### Personnalisation

Modifiez les paramètres dans `Controller/captcha-config.php` :

```php
const CAPTCHA_LENGTH = 6; // Longueur du code
const CAPTCHA_IMAGE_WIDTH = 250; // Largeur de l'image
const CAPTCHA_IMAGE_HEIGHT = 80; // Hauteur de l'image
const CAPTCHA_NOISE_LINES = 5; // Nombre de lignes de bruit
const CAPTCHA_NOISE_POINTS = 100; // Nombre de points de bruit
```

### Intégration dans d'autres pages

Pour ajouter le CAPTCHA à d'autres formulaires :

1. **Incluez la fonction** dans `View/auth.php` :
```php
function generateCaptchaCode(int $length = 6): string { ... }
```

2. **Initialisez le CAPTCHA** au chargement de la page :
```php
if (!isset($_SESSION['captcha_code'])) {
    $_SESSION['captcha_code'] = generateCaptchaCode();
}
```

3. **Validez lors de la soumission** :
```php
$captcha = strtoupper($_POST['captcha'] ?? '');
if ($captcha !== $_SESSION['captcha_code']) {
    $errors['captcha'] = 'Code CAPTCHA incorrect';
    unset($_SESSION['captcha_code']);
}
```

### Dépannage

**Problème** : Le CAPTCHA n'affiche pas d'image
- **Solution** : Vérifiez que la GD Library est activée (`php.ini` : `extension=gd`)

**Problème** : Le son ne fonctionne pas
- **Solution** : Vérifiez que le navigateur autorise l'audio et le type MIME WAV

**Problème** : La validation du CAPTCHA échoue toujours
- **Solution** : Vérifiez que la session PHP est correctement configurée et que les cookies sont activés

**Problème** : L'image CAPTCHA se rafraîchit trop souvent
- **Solution** : Vérifiez que le bouton "Rafraîchir" a l'attribut `type="button"` et non `type="submit"`

---

## Sécurité

### Points forts du CAPTCHA

✅ Code aléatoire généré serveur-side
✅ Bruits et distorsions pour prévenir l'OCR (Optical Character Recognition)
✅ Validation serveur robuste
✅ Cache désactivé pour éviter les rejeux
✅ Régénération après tentative échouée
✅ Support audio pour l'accessibilité

### Recommandations

- ✅ Utilisez HTTPS en production
- ✅ Activez les sessions sécurisées (secure/httponly cookies)
- ✅ Limitez le nombre de tentatives par IP si nécessaire
- ✅ Surveillez les logs d'erreur pour détecter les attaques

---

## Questions fréquemment posées

**Q: Pourquoi le CAPTCHA régenère-t-il après une tentative échouée ?**
R: C'est une mesure de sécurité pour empêcher les attaques par force brute.

**Q: Le CAPTCHA fonctionne-t-il sur mobile ?**
R: Oui, le design est responsive et fonctionne sur tous les appareils.

**Q: Puis-je désactiver le CAPTCHA ?**
R: Non recommandé. Vous pouvez commenter la validation dans `Controller/auth.php` mais ce n'est pas conseillé pour la sécurité.

**Q: Comment ajouter le CAPTCHA à d'autres formulaires ?**
R: Consultez la section "Intégration dans d'autres pages" ci-dessus.

