<?php

require_once __DIR__ . '/../../vendor/autoload.php';

use App\Models\User;
use Dotenv\Dotenv;

//global $argv;
$dotenv = Dotenv::createImmutable(__DIR__ . '/../../');
$dotenv->load();

$user = new User();
$result = $user->insert(
    [
        'name' => 'John',
        'email' => 'test@testdomain.com',
        'password' => 'test'
    ]
);
echo "Data inserted".PHP_EOL;
