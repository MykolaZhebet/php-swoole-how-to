<?php

require __DIR__ . '/vendor/autoload.php';
use Swoole\Http\Request;
use Swoole\Http\Response;
use \Swoole\WebSocket\Server;
$server = new Server( '0.0.0.0', 8004);
$server->on('start', function (Server $server) {
    echo 'Swoole WebSocket Server is started at http://127.0.0.1:8004' . PHP_EOL;
});

$server->on('message', function (Server $server, \Swoole\WebSocket\Frame $frame) {
    echo 'Received message: ' . $frame->data.PHP_EOL;
    //Broadcast to all clients
    foreach($server->connections as $fd) {
        if(!$server->isEstablished($fd)) {
            continue;
        }
        $server->push($fd, $frame->data);
    }
});


$server->on('request', function(Request $request, Response $response) {
    $templates = new \League\Plates\Engine(__DIR__ . '/src/Views');
//    $respose->getBody()->write();
    $response->header('Content-Type', 'text/html');
    $response->end($templates->render('home'));
});

$server->start();
