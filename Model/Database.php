<?php

declare(strict_types=1);

require_once __DIR__ . '/config.php';

final class Database
{
    public static function connection(): PDO
    {
        return getPdo();
    }
}
