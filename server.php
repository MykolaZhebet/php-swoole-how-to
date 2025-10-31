<?php
declare(strict_types=1);
error_reporting(E_ALL & ~E_DEPRECATED);
use \App\Bootstrap\App;
global $app, $requestConverter, $application;
const ROOT_DIR = __DIR__;
require __DIR__ . '/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

App::start();