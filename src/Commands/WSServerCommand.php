<?php

namespace App\Commands;

use Ilex\SwoolePsr7\SwooleResponseConverter;
use Swoole\Http\Request;
use Swoole\Http\Response;
use \Swoole\WebSocket\Server;
use Symfony\Component\Console\Command\Command;
use \Swoole\WebSocket\Frame;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use League\Plates\Engine;
class WSServerCommand extends Command
{
    protected static $defaultName = 'ws-server:start';
    protected static $defaultDescription = 'Run WS server';

    protected function configure() {
        $this
            ->setName(self::$defaultName)
            ->setDescription(self::$defaultDescription)
            ->setHelp(self::$defaultDescription);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int {
        $io = new SymfonyStyle($input, $output);
        $this->start($io);
        return Command::SUCCESS;
    }

    protected function start(SymfonyStyle $io) {
        global $app, $requestConverter;

        $wsServerHost = (string)$_SERVER['SWOOLE_WS_SERVER_HOST'] ?: '0.0.0.0';
        $wsServerPort = (int)$_SERVER['SWOOLE_WS_SERVER_PORT'] ?: 8004;
        $io->info('Starting WebSocket Server');
        $server = new Server( $wsServerHost, $wsServerPort);
        $server->on('start', function (Server $server) use ($io, $wsServerHost, $wsServerPort) {
            $io->success(
                sprintf('Swoole WebSocket Server is started at ws://%s:%d', $wsServerHost, $wsServerPort)
            );
        });

        $server->on('message', function (Server $server, Frame $frame) use ($io) {
            echo 'Received message: ' . $frame->data.PHP_EOL;
            //Broadcast to all clients
            $io->info('Broadcasting message: ' . $frame->data);
            foreach($server->connections as $fd) {
                if(!$server->isEstablished($fd)) {
                    continue;
                }
                $server->push($fd, $frame->data);
            }
        });


        $server->on('request', function(Request $request, Response $response) {
            $templates = new Engine(ROOT_DIR . '/src/Views');
            $response->header('Content-Type', 'text/html');
            $response->end($templates->render('home'));
        });

        $server->start();
    }

}