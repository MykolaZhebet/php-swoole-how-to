<?php
declare(strict_types=1);

use Swoole\Http\Request;
use Swoole\Http\Response;
use Swoole\Http\Server;


$serverHost = $_SERVER['SWOOLE_SERVER_HOST'] ?: '0.0.0.0';
$serverPort = (int)$_SERVER['SWOOLE_SERVER_PORT'] ?: 8003;
$server = new Server($serverHost, $serverPort);
echo "Run on PHP ".phpversion().PHP_EOL;

echo  "swoole remote address: ". $_SERVER['SWOOLE_SERVER_HOST']. ' swoole port: '. $_SERVER['SWOOLE_SERVER_PORT'].PHP_EOL;

$user = 'anonymous';
$server->on('start', function (Server $server ) {
    echo "swoole http server is started at http://0.0.0.0:8003\n";
});

$server->on('request', function (Request $request, Response $response) use (&$user) {
    $params = [];

    if(isset($request->server['query_string'])) {
        parse_str($request->server['query_string'], $params);
    }

    if (isset($params['name'])) {
        $user = $params['name'];
    }

    $response->end("Hello, $user Response from swoole server");

});

$server->start();