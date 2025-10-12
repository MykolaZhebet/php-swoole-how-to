<?php
declare(strict_types=1);

use Swoole\Http\Request;
use Swoole\Http\Response;
use Swoole\Http\Server;


$server = new Server('0.0.0.0', 8003);
echo "Run on PHP ".phpversion().PHP_EOL;

echo  "Swoole remote address: ". $_SERVER['SWOOLE_SERVER_HOST']. ' swoole port: '. $_SERVER['SWOOLE_SERVER_PORT'].PHP_EOL;

$server->on('start', function (Server $server ) {
    echo "Swoole http server is started at http://0.0.0.0:8003\n";
});

$server->on('request', function (Request $request, Response $response) {
    $response->end("Response from Swoole server");
});

$server->start();