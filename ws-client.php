<?php

use Kanata\ConveyorServerClient\Client;

require_once __DIR__ . '/vendor/autoload.php';
echo 'Start client'. PHP_EOL;
$wsClient = new Client([
    'port' => 8004,
    'onMessageCallback' => function($client, $message) {
        //Will be called on every message
        echo('message is: '. $message);
        $parsedMessage = json_decode($message, true);
        if ('hello' === $parsedMessage['data']) {
        echo('sending secondary broadcast action from BE client'.PHP_EOL);
            $client->send(json_encode([
//                'action' => 'broadcast-action',
                'action' => 'fanout-action',
                'data' => 'hello from BE client!!!'
            ]));
        }
    },
    'channel' => 'sample-channel',
//    'listen' => ['broadcast-action'],
    'listen' => ['fanout-action'],
]);
$wsClient->connect();
//$wsClient->send('hello');
