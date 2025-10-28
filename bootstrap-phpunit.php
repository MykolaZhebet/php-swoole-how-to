<?php

require __DIR__ . '/vendor/autoload.php';
const ROOT_DIR = __DIR__;
global $app;
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();
