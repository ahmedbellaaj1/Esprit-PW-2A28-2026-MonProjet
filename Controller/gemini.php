<?php
// Controller/gemini.php — Clé API Google Gemini Vision
// Obtenez votre clé gratuite sur : https://aistudio.google.com/apikey

if (!defined('GEMINI_API_KEY')) {
    define('GEMINI_API_KEY', 'AIzaSyA1S4KCgBlvso_umwZzKSTBLJeRnFGv0sA');
}
// gemini-2.0-flash : stable, rapide, supporte la vision multimodale
if (!defined('GEMINI_API_URL')) {
    define('GEMINI_API_URL', 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent');
}
