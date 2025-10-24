<?php
namespace App\Bootstrap;

use Nyholm\Psr7\Factory\Psr17Factory;
use Ilex\SwoolePsr7\SwooleServerRequestConverter;
use Slim\App as SlimApp;
class App  {
    public static function start(): void {
        [$app, $requestConverter] = App::prepareSlimApp();

        Dependencies::start($app);

        (require ROOT_DIR . '/src/routes.php')($app);

        SwooleServer::start($app, $requestConverter);
    }

    /**
     * @return array{ 0: SlimApp, 1: SwooleServerRequestConverter }
     */
    private static function prepareSlimApp(): array {

        $psr17Factory = new Psr17Factory();

        $requestConverter = new SwooleServerRequestConverter(
            $psr17Factory, $psr17Factory, $psr17Factory, $psr17Factory
        );

        $app = new SlimApp($psr17Factory, new \DI\Container());
        $app->addRoutingMiddleware();
        $app->addErrorMiddleware(true, true, true);

        return [$app, $requestConverter];
    }
}