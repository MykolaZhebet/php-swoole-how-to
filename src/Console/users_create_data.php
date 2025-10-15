<?php

require_once __DIR__ . '/../../vendor/autoload.php';

use App\Models\User;
use Dotenv\Dotenv;
$dotenv = Dotenv::createImmutable(__DIR__ . '/../../');
$dotenv->load();

$user = new User();
if ($user->createTable()) {
    echo 'Table created successfully'. PHP_EOL;
    exit;
}

echo "Table already exists".PHP_EOL;
