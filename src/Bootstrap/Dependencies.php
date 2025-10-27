<?php
namespace App\Bootstrap;

use Illuminate\Database\Capsule\Manager as Capsule;
use Slim\App;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
class Dependencies {
    public static function start(App $app): void {
        self::registerLogger($app);
        self::registerDBCapsule($app);
    }

    private static function registerLogger(App $app): void {
        $app->getContainer()->set('logger', function () {
            $logger = new Logger('slim-app');
            $logger->pushhandler(new StreamHandler('php://stdout', Logger::DEBUG));
            return $logger;
        });
    }

    private static function registerDBCapsule(App $app) {
        $container = $app->getContainer();
        $container->set('db', function () {
            $capsule = new Capsule();
            $capsule->addConnection([
                'driver' => $_ENV['DB_DRIVER'],
                'host' => $_ENV['DB_HOST'],
                'database' => $_ENV['DB_NAME'],
                'username' => $_ENV['DB_USER'],
                'password' => $_ENV['DB_PASSWORD'],
                'charset' => $_ENV['DB_CHARSET'] ?? 'utf8',
                'collation' => $_ENV['DB_COLLATION'] ?? 'utf8_unicode_ci',
                'prefix' => $_ENV['DB_PREFIX'] ?? '',
            ]);
            $capsule->setAsGlobal();
            $capsule->bootEloquent();
            return $capsule;
        });
        //Start db connection
        $container->get('db');
    }
}