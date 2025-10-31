<?php

require_once __DIR__ . '/../../vendor/autoload.php';

use App\Models\User;
use Dotenv\Dotenv;

//global $argv;
$dotenv = Dotenv::createImmutable(__DIR__ . '/../../');
$dotenv->load();

$users = [];
//$user = User::create();
$user = new User();
$users[] = $user->insert(
    [
        'name' => 'John',
        'email' => 'test@testdomain.com',
        'password' => 'test'
    ]
);
$user = new User();
$users[] = $user->insert(
    [
        'name' => 'Mike1',
        'email' => 'test1@testdomain.com',
        'password' => 'test'
    ]
);
if (count($users) !== count(array_filter($users))) {
    echo "Failed to insert data".PHP_EOL;
} else {
    echo "Data inserted".PHP_EOL;

}
