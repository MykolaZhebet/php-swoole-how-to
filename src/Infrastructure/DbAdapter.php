<?php

namespace App\Infrastructure;

use PDO;

class DbAdapter
{

    public static function getPdo()
    {
        $host = $_ENV['DB_HOST'];
        $port = $_ENV['DB_PORT'];
        $user = $_ENV['DB_USER'];
        $pass = $_ENV['DB_PASSWORD'];
        $db = $_ENV['DB_NAME'];
        $dsn = sprintf("mysql:host=%s;port=%s;dbname=%s;charset=utf8", $host, $port, $db);

        return new PDO($dsn, $user, $pass);
    }

    public static function execute(string $query, ?array $params = null): bool
    {
        $pdo = self::getPdo();
        $statement = $pdo->prepare($query);
        $result = $statement->execute($params);
        $pdo = null;
        return $result;
    }

    public static function fetchAll(string $query, array $params)
    {
        $pdo = self::getPdo();
        $statement = $pdo->prepare($query);
        $statement->execute($params);
        $data = $statement->fetchAll();
        $pdo = null;
        return $data;
    }
}