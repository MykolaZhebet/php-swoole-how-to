<?php
namespace App\Bootstrap;

use App\Services\Event;
use Swoole\Http\Server;
use Swoole\Http\Request;
use Swoole\Http\Response;
use Ilex\SwoolePsr7\SwooleResponseConverter;
use Slim\App;
use Ilex\SwoolePsr7\SwooleServerRequestConverter;
use Swoole\Table;
use Swoole\Timer;

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

        self::eventsHandle($app);


        $server->start();
    }

    protected static function eventsHandle(App $app): void {
        $table = new Table(1024);
        $table->column('eventName', Table::TYPE_STRING, 40);
        $table->column('eventData', Table::TYPE_STRING, 250);
        $table->create();
        $app->getContainer()->set('eventTable', $table);

        Timer::tick(1000, function () use ($table, $app) {
            $eventList = (Event::getInstance())->getListeners();
            /** @var \Monolog\Logger $logger */
            $logger = $app->getContainer()->get('logger');
//            $logger->info('Processing events ('.count($table).')');
            foreach($table AS $key => $event) {
                if (!isset($eventList[$event['eventName']])) {
                    $logger->warning('Event ' . $event['eventName'] . ' not registered');
                    continue;
                }
                foreach($eventList[$event['eventName']] as $handler) {
                    $handler($event['eventData']);
                }
                $table->del($key);
            }
        });
    }
}
