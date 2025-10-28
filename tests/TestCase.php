<?php

namespace Tests;
use App\Bootstrap\Dependencies;
use App\Infrastructure\Migration;
use App\Infrastructure\Seed;
use Nyholm\Psr7\Factory\Psr17Factory;
use \PHPUnit\Framework\TestCase as BaseTestCase;
use Slim\App;
use App\Bootstrap\App as AppBootstrap;
class TestCase extends BaseTestCase
{
    public function getApp(): App {
        global $app;
        $psr17Factory = new Psr17Factory();
        $app = new App($psr17Factory, new \DI\Container());
        $app->addRoutingMiddleware();
        Dependencies::start($app);
        AppBootstrap::registerRoutes($app);

        Migration::handle($app, true);
        Seed::handle($app);

        return $app;

    }

}
