<?php

namespace App\Commands;

use App\Models\Token;
use App\Models\User;
use App\Services\JwtToken;
use Ilex\SwoolePsr7\SwooleResponseConverter;
use Swoole\Http\Request;
use Swoole\Http\Response;
use Swoole\Table;
use \Swoole\WebSocket\Server;
use Symfony\Component\Console\Command\Command;
use \Swoole\WebSocket\Frame;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use League\Plates\Engine;
class WSServerCommand extends Command
{
    protected static $defaultName = 'ws-server:start';
    protected static $defaultDescription = 'Run WS server';
    private Table $userTable;

    protected function configure() {
        $this
            ->setName(self::$defaultName)
            ->setDescription(self::$defaultDescription)
            ->setDefinition([
                new InputOption('port', null, InputOption::VALUE_OPTIONAL, 'The port to run the server on', 8004),
                new InputOption('http', null, InputOption::VALUE_OPTIONAL, 'Run the WS server in HTTP mode also', true),
            ])
            ->setHelp(self::$defaultDescription);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int {
        $this->start(
            new SymfonyStyle($input, $output),
            $input->getOption('port'),
            $input->getOption('http')
        );
        return Command::SUCCESS;
    }

    protected function start(SymfonyStyle $io, int $port, bool $http) {
        global $app, $requestConverter;
        $this->startUserTable();
        $app->getContainer()->set('ws-context', [
            'port' => $port,
        ]);
        $wsServerHost = (string)$_SERVER['SWOOLE_WS_SERVER_HOST'] ?: '0.0.0.0';
        $wsServerPort = (int)$_SERVER['SWOOLE_WS_SERVER_PORT'] ?: 8004;
        $wsServerPort = $port ? : $wsServerPort;
        $io->info('Starting WebSocket Server');
        $server = new Server( $wsServerHost, $wsServerPort);
        $server->on('start', function (Server $server) use ($io, $wsServerHost, $wsServerPort, $http) {
            $io->success(
                sprintf('Swoole WebSocket Server is started at ws://%s:%d', $wsServerHost, $wsServerPort)
            );

            if($http) {
                $io->info('Starting HTTP Server');
            }
        });

        $server->on('message', function (Server $server, Frame $frame) use ($io) {
            $io->info('Received message: ' . $frame->data);
            //Broadcast to all clients
            $io->info('Broadcasting message: ' . $frame->data);
            foreach($server->connections as $fd) {
                if(!$server->isEstablished($fd)) {
                    continue;
                }
                $server->push($fd, json_encode([
                    'user' => $this->userTable->get($fd, 'userName'),
                    'message' => $frame->data,
                ]));
            }
        });

        $server->on('open', function (Server $server, Request $request) use ($io){
            if(
                !isset($request->get['token'])
                || !$this->identifyUser($request->get['token'], $request->fd)
            ) {
                $io->error('Invalid token, closing FD connection '. $request->fd. ' token: '.$request->get['token']);
                $server->disconnect($request->fd, 1003, 'Invalid token');
                return;
            }

            $io->info('User connected: ' . $request->fd);
        });

        $server->on('disconnect', function (Server $server, int $fd) use ($io) {
            //Clear user data
            $io->info('User disconnected: ' . $fd);
            if ($this->userTable->exists($fd)) {
                $this->userTable->del($fd);
            }
        });

//        $server->on('request', function(Request $request, Response $response) {
//            $templates = new Engine(ROOT_DIR . '/src/Views');
//            $response->header('Content-Type', 'text/html');
//            $response->end($templates->render('home'));
//        });
        if($http) {
            $server->on('request', function (Request $request, Response $response) use ($app, $requestConverter) {
                $psr7Request = $requestConverter->createFromSwoole($request);
                $psr7Response = $app->handle($psr7Request);
                $converter = new SwooleResponseConverter($response);
                $converter->send($psr7Response);
            });
        }
        $server->set([
            'document_root' => ROOT_DIR . '/public',
            'enable_static_handler' => true,
            'static_handler_locations' => ['/js'],
        ]);
        $server->start();
    }

    private function identifyUser(string $token, int $fd): bool {
        global $app;
        $logger = $app->getContainer()->get('logger');

        try {
            //Read once, mark as consumed, throw exception if used
            $tokenRecord = Token::where('token', $token)->first()->consume();
        } catch(\Exception $e){
            $logger->error('Error identifying user: ' . $e->getMessage());
            return false;
        }

        $tokenDecoded = JwtToken::decodeToken($token, $tokenRecord->name);
        if(!isset($tokenDecoded['user_id'])) {
            $logger->error('Invalid token(token doesn\'t have user_id)');
            return false;
        }

        $user = User::find($tokenDecoded['user_id']);
        if (is_null($user)) {
            $logger->error('Invalid token(user not found)');
            return false;
        }

        $logger->info('User identified: ' . $user->email);
        return $this->userTable->set($fd, [
            'userId' => $user->id,
            'userName' => $user->name,
        ]);
    }

    private function startUserTable() {
        //Id will be FD(file descriptor from the socket)
        $userTable = new Table(1024);
        $userTable->column('userId', Table::TYPE_INT, 4);
        $userTable->column('userName', TABLE::TYPE_STRING, 255);
        $userTable->create();
        $this->userTable = $userTable;
    }
}