<?php

declare(strict_types=1);

namespace App;

use PDO;
use PDOException;

final class Database
{
    public static function fromConfig(array $db): PDO
    {
        $host = $db['host'] ?? '127.0.0.1';
        $port = (int) ($db['port'] ?? 3306);
        $name = $db['name'] ?? 'couple_finance';
        $user = $db['user'] ?? 'root';
        $password = $db['password'] ?? '';

        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];

        $dsnPrimary = sprintf('mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4', $host, $port, $name);
        try {
            return new PDO($dsnPrimary, $user, $password, $options);
        } catch (PDOException $primaryException) {
            $altHost = null;
            if ($host === 'localhost') {
                $altHost = '127.0.0.1';
            } elseif ($host === '127.0.0.1') {
                $altHost = 'localhost';
            }

            if ($altHost === null) {
                throw $primaryException;
            }

            $dsnFallback = sprintf('mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4', $altHost, $port, $name);
            return new PDO($dsnFallback, $user, $password, $options);
        }
    }
}
