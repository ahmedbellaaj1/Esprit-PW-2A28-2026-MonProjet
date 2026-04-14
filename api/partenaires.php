<?php
// api/partenaires.php  — Point d'entrée API pour les partenaires
require_once __DIR__ . '/../controllers/PartenaireController.php';
(new PartenaireController())->handle();
