<?php
// api/dons.php  — Point d'entrée API pour les dons
require_once __DIR__ . '/../controllers/DonController.php';
(new DonController())->handle();
