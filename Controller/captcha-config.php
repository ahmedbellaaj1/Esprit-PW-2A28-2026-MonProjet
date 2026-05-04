<?php

/**
 * Configuration CAPTCHA
 * 
 * Ces paramètres contrôlent le comportement du système CAPTCHA
 */

// Longueur du code CAPTCHA (nombre de caractères)
const CAPTCHA_LENGTH = 6;

// Caractères disponibles dans le CAPTCHA
const CAPTCHA_CHARACTERS = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';

// Paramètres de l'image
const CAPTCHA_IMAGE_WIDTH = 250;
const CAPTCHA_IMAGE_HEIGHT = 80;

// Nombre de lignes de bruit
const CAPTCHA_NOISE_LINES = 5;

// Nombre de points de bruit
const CAPTCHA_NOISE_POINTS = 100;

// Fréquence de base pour l'audio (en Hz)
const CAPTCHA_AUDIO_BASE_FREQUENCY = 440;

// Durée de chaque son (en secondes)
const CAPTCHA_AUDIO_DURATION = 0.3;

// Taux d'échantillonnage audio (en Hz)
const CAPTCHA_AUDIO_SAMPLE_RATE = 22050;

// Temps d'expiration du CAPTCHA (en secondes)
// 0 = pas d'expiration
const CAPTCHA_EXPIRATION = 600; // 10 minutes

// Nombre maximum de tentatives avant blocage
// 0 = pas de limite
const CAPTCHA_MAX_ATTEMPTS = 5;

// Message d'erreur CAPTCHA personnalisé
const CAPTCHA_ERROR_MESSAGE = 'Le code CAPTCHA est incorrect. Veuillez reessayer.';

// Message d'erreur CAPTCHA manquant
const CAPTCHA_REQUIRED_MESSAGE = 'Le CAPTCHA est obligatoire.';

?>
