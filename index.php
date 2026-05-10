<?php

declare(strict_types=1);

require_once __DIR__ . '/Controller/bootstrap.php';

if (isset($_SESSION['user'])) {
    if (($_SESSION['user']['role'] ?? '') === 'admin') {
        header('Location: /Green-Bite/View/back-office/dashboard.php');
    } else {
        header('Location: /Green-Bite/View/front-office/index.php');
    }
} else {
    header('Location: /Green-Bite/View/auth.php');
}
exit;
