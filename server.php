<?php
declare(strict_types=1);

//use App\Controllers\HomeController;
use App\Application\Middlewares\CheckUsersExistenceMiddleware;
use App\Application\Middlewares\SessionMiddleware;
use App\Controllers\HomeController;
use Ilex\SwoolePsr7\SwooleResponseConverter;
use Ilex\SwoolePsr7\SwooleServerRequestConverter;
use League\Plates\Engine;

use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Nyholm\Psr7\Factory\Psr17Factory;
use Psr\Http\Message\{RequestInterface, ResponseInterface};
use Slim\App;
use Slim\Routing\RouteCollectorProxy;
use Swoole\Http\Server;
use Swoole\Http\Request;
use Swoole\Http\Response;

const ROOT_DIR = __DIR__;
require __DIR__ . '/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

$psr17Factory = new Psr17Factory();

$requestConverter = new SwooleServerRequestConverter(
    $psr17Factory, $psr17Factory, $psr17Factory, $psr17Factory
);

$app = new App($psr17Factory, new \DI\Container());
$app->addRoutingMiddleware();
$container = $app->getContainer();

$container->set('logger', function () {
    $logger = new Logger('slim-app');
    $logger->pushhandler(new StreamHandler('php://stdout', Logger::DEBUG));
    return $logger;
});


$errorMiddleware = $app->addErrorMiddleware(true, true, true);
//$app->setBasePath('/var/www');
$app->get('/', HomeController::class . ':welcome');

$app->group('/users',function(RouteCollectorProxy $group) {
    $group->get('', HomeController::class . ':showUsers');
    $group->get('/{id:[0-9]+}', HomeController::class . ':showUser')->add(new CheckUsersExistenceMiddleware());
})->add(new SessionMiddleware());


// Or using BasePathMiddleware
// $app->add(new BasePathMiddleware($app));

$serverHost = $_SERVER['SWOOLE_SERVER_HOST'] ?: '0.0.0.0';
$serverPort = (int)$_SERVER['SWOOLE_SERVER_PORT'] ?: 8003;

$server = new Server($serverHost, $serverPort);
$server->on('start', function (Server $server) {
    echo "swoole http server is started at http://0.0.0.0:8003\n";
});

$server->on('request', function (Request $request, Response $response) use ($app, $requestConverter) {
    $psr7Request = $requestConverter->createFromSwoole($request);
    $psr7Response = $app->handle($psr7Request);
    $converter = new SwooleResponseConverter($response);
    $converter->send($psr7Response);
});

$server->start();