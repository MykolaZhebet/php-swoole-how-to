<?php
declare(strict_types=1);

//use App\Controllers\HomeController;
use App\Application\Middlewares\CheckUsersExistenceMiddleware;
use App\Controllers\HomeController;
use Ilex\SwoolePsr7\SwooleResponseConverter;
use Ilex\SwoolePsr7\SwooleServerRequestConverter;
use League\Plates\Engine;

use Nyholm\Psr7\Factory\Psr17Factory;
use Psr\Http\Message\{RequestInterface, ResponseInterface};
use Slim\App;
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

$app = new App($psr17Factory);

$app->addRoutingMiddleware();
$errorMiddleware = $app->addErrorMiddleware(true, true, true);
//$app->setBasePath('/var/www');
$app->get('/', HomeController::class . ':welcome');
$app->get('/user/{id}', HomeController::class . ':showUser')->add(new CheckUsersExistenceMiddleware());
//$app->get('/', function (RequestInterface $request, ResponseInterface $response, $args) {
//    error_log('Init route from Slim!!');
//    $templates = new Engine(__DIR__ . '/Views');
//    $response->getBody()->write($templates->render('view1', ['testVar' => 'Hello World!']));
//    return $response;
//})->setName('root');



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