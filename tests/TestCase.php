<?php

namespace Tests;
use App\Bootstrap\Dependencies;
use App\Commands\GenerateFactoryCommand;
use App\Commands\GenerateJwtToken;
use App\Commands\MigrateCommand;
use App\Infrastructure\Migration;
use App\Infrastructure\Seed;
use App\Infrastructure\Services\Session;
use App\Infrastructure\Services\SessionTable;
use League\Flysystem\Filesystem;
use Mockery;
use Nyholm\Psr7\Factory\Psr17Factory;
use \PHPUnit\Framework\TestCase as BaseTestCase;
use Slim\App;
use App\Bootstrap\App as AppBootstrap;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Nekofar\Slim\Test\TestResponse;

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
        $application->add(new GenerateFactoryCommand());
    }

    public function runCommand(string $commandName, $args = []): CommandTester {
        global $application;
        $command = $application->find($commandName);
        $tester = new CommandTester($command);
        $tester->execute($args);
        return $tester;
    }

    public function getSessionCookieFromResponse(TestResponse $response): array {
        $cookie = current($response->getHeader('Set-Cookie'));
        parse_str($cookie, $parsedCookie);
        $parsedCookie = current(explode(';', current($parsedCookie)));
        $parsedCookie = Session::parseCookie($parsedCookie);
        return SessionTable::getInstance()->get($parsedCookie['id']);
    }

    public function getCookieParams(TestResponse $response): array {
        $parsedCookie = explode('=', current($response->getHeader('Set-Cookie')));
        $cookieKey = $parsedCookie[0];
        unset($parsedCookie[0]);
        $cookie = current(explode(';', $parsedCookie[1]));
        return [$cookieKey => $cookie];
    }

    public function mockFileSystem(): void {
        global $app;

        $container = $app->getContainer();
        $container->set('filesystem', function() {
            return Mockery::mock(Filesystem::class);
        });
    }
}
