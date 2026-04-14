<?php

declare(strict_types=1);

function redirect(string $url): void
{
    header('Location: ' . $url);
    exit;
}

function h(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}
