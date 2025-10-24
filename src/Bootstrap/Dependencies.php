<?php
namespace App\Bootstrap;

use Slim\App;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
class Dependencies {
    public static function start(App $app): void {
        self::registerLogger($app);
    }

    private static function registerLogger(App $app): void {
        $app->getContainer()->set('logger', function () {
            $logger = new Logger('slim-app');
            $logger->pushhandler(new StreamHandler('php://stdout', Logger::DEBUG));
            return $logger;
        });
    }
}