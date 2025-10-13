<?php
//declare(strict_types=1);
//
//use Swoole\Http\Request;
//use Swoole\Http\Response;
//use Swoole\Http\Server;
//
//
//$serverHost = $_SERVER['SWOOLE_SERVER_HOST'] ?: '0.0.0.0';
//$serverPort = (int)$_SERVER['SWOOLE_SERVER_PORT'] ?: 8003;
//$server = new Server($serverHost, $serverPort);
//echo "Run on PHP ".phpversion().PHP_EOL;
//
//echo  "Swoole remote address: ". $_SERVER['SWOOLE_SERVER_HOST']. ' swoole port: '. $_SERVER['SWOOLE_SERVER_PORT'].PHP_EOL;
//
//$user = 'anonymous';
//$server->on('start', function (Server $server ) {
//    echo "Swoole http server is started at http://0.0.0.0:8003\n";
//});
//
//$server->on('request', function (Request $request, Response $response) use (&$user) {
//    $params = [];
//
//    if(isset($request->server['query_string'])) {
//        parse_str($request->server['query_string'], $params);
//    }
//
//    if (isset($params['name'])) {
//        $user = $params['name'];
//    }
//
//    $response->end("Hello, $user Response from Swoole server");
//
//});
//
//$server->start();


use Ilex\SwoolePsr7\SwooleResponseConverter;
use Ilex\SwoolePsr7\SwooleServerRequestConverter;
use League\Plates\Engine;

use Nyholm\Psr7\Factory\Psr17Factory;
use Psr\Http\Message\{RequestInterface, ResponseInterface};
use Slim\App;
use Swoole\Http\Server;
use Swoole\Http\Request;
use Swoole\Http\Response;

require __DIR__ . '/vendor/autoload.php';

$psr17Factory = new Psr17Factory();

$requestConverter = new SwooleServerRequestConverter(
    $psr17Factory, $psr17Factory, $psr17Factory, $psr17Factory
);

$app = new App($psr17Factory);

$app->addRoutingMiddleware();
$errorMiddleware = $app->addErrorMiddleware(true, true, true);
//$app->setBasePath('/var/www');
$app->get('/', function (RequestInterface $request, ResponseInterface $response, $args) {
    error_log('Init route from Slim!!');
    $templates = new Engine(__DIR__ . '/views');
    $response->getBody()->write($templates->render('view1', ['testVar' => 'Hello World!']));
    return $response;
})->setName('root');



// Or using BasePathMiddleware
// $app->add(new BasePathMiddleware($app));

$serverHost = $_SERVER['SWOOLE_SERVER_HOST'] ?: '0.0.0.0';
$serverPort = (int)$_SERVER['SWOOLE_SERVER_PORT'] ?: 8003;

$server = new Server($serverHost, $serverPort);
$server->on('start', function (Server $server) {
    echo "Swoole http server is started at http://0.0.0.0:8003\n";
});

$server->on('request', function (Request $request, Response $response) use ($app, $requestConverter) {
    $psr7Request = $requestConverter->createFromSwoole($request);
    $psr7Response = $app->handle($psr7Request);
    $converter = new SwooleResponseConverter($response);
    $converter->send($psr7Response);
});

$server->start();