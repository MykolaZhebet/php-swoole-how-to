<?php
namespace App\Bootstrap;

use Swoole\Http\Server;
use Swoole\Http\Request;
use Swoole\Http\Response;
use Ilex\SwoolePsr7\SwooleResponseConverter;
use Slim\App;
use Ilex\SwoolePsr7\SwooleServerRequestConverter;
class SwooleServer {
    public static function start(App $app, SwooleServerRequestConverter $requestConverter): void {
        $serverHost = $_SERVER['SWOOLE_SERVER_HOST'] ?: '0.0.0.0';
        $serverPort = (int)$_SERVER['SWOOLE_SERVER_PORT'] ?: 8003;

        $server = new Server($serverHost, $serverPort);
        $server->on('start', function (Server $server) use ($serverHost, $serverPort) {
            echo sprintf("swoole http server is started at http://%s:%d", $serverHost, $serverPort).PHP_EOL;
        });

        $server->on('request', function (Request $request, Response $response) use ($app, $requestConverter) {
            $psr7Request = $requestConverter->createFromSwoole($request);
            $psr7Response = $app->handle($psr7Request);
            $converter = new SwooleResponseConverter($response);
            $converter->send($psr7Response);
        });

        $server->start();
    }
}
