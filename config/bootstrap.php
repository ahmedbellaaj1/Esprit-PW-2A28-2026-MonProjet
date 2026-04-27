<?php
declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Chemin URL de la racine du projet (ex: /GreenBite) pour les liens assets.
 */
function project_web_base(): string
{
    $doc = realpath($_SERVER['DOCUMENT_ROOT'] ?? '');
    $root = realpath(__DIR__ . '/..');
    if ($doc === false || $root === false) {
        return '';
    }
    $doc = str_replace('\\', '/', $doc);
    $root = str_replace('\\', '/', $root);
    if (!str_starts_with($root, $doc)) {
        return '';
    }
    $rel = substr($root, strlen($doc));
    return rtrim($rel, '/') === '' ? '' : rtrim($rel, '/');
}
