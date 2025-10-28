<?php

namespace Tests;
use App\Bootstrap\Dependencies;
use App\Commands\GenerateJwtToken;
use App\Commands\MigrateCommand;
use App\Infrastructure\Migration;
use App\Infrastructure\Seed;
use Nyholm\Psr7\Factory\Psr17Factory;
use \PHPUnit\Framework\TestCase as BaseTestCase;
use Slim\App;
use App\Bootstrap\App as AppBootstrap;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

class TestCase extends BaseTestCase
{
    public function getApp(): App {
        global $app;
        $psr17Factory = new Psr17Factory();
        $app = new App($psr17Factory, new \DI\Container());
        $app->addRoutingMiddleware();
        Dependencies::start($app);
        AppBootstrap::registerRoutes($app);

        $this->prepareApplicationCommands();
        $this->runCommand('migration:migrate', ['--fresh' => true]);
//        Migration::handle($app, true);
        Seed::handle($app);

        return $app;
    }

    public function prepareApplicationCommands(): void {
        global $application;
        $application = new Application();
        $application->add(new MigrateCommand());
        $application->add(new GenerateJwtToken());
    }

    public function runCommand(string $commandName, $args = []): CommandTester {
        global $application;
        $command = $application->find($commandName);
        $tester = new CommandTester($command);
        $tester->execute($args);
        return $tester;
    }

}
