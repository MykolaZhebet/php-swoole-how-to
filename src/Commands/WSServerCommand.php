<?php

namespace App\Commands;

use App\Controllers\WS\ClosedConnectionAction;
use App\Controllers\WS\NewConnectionAction;
use App\Controllers\WS\SecondaryBroadcastAction;
use App\Controllers\WS\WelcomeAction;
use App\Models\Token;
use App\Models\User;
use App\Services\JwtToken;
use Conveyor\Models\SocketChannelPersistenceTable;
use Conveyor\Models\SocketListenerPersistenceTable;
use Conveyor\SocketHandlers\SocketMessageRouter;
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
    
    private SymfonyStyle $io;

    protected function configure() {
        $this
            ->setName(self::$defaultName)
            ->setDescription(self::$defaultDescription)
            ->setDefinition([
                new InputOption('port', null, InputOption::VALUE_OPTIONAL, 'The port to run the server on', 8004),
                new InputOption('http', null, InputOption::VALUE_OPTIONAL, 'Run the WS server in HTTP mode also', true),
                new InputOption('enableSSL', null, InputOption::VALUE_OPTIONAL, 'Enable SSL', true),
            ])
            ->setHelp(self::$defaultDescription);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int {
        $this->io = new SymfonyStyle($input, $output);
        $this->start(
            $input->getOption('port'),
            $input->getOption('http'),
            $input->getOption('enableSSL')
        );
        return Command::SUCCESS;
    }

    protected function start(int $port, bool $http, bool $enableSSL) {
        global $app, $requestConverter;
        $this->startUserTable();
        $app->getContainer()->set('ws-context', [
            'port' => $port,
        ]);
        $wsServerHost = (string)$_SERVER['SWOOLE_WS_SERVER_HOST'] ?: '0.0.0.0';
        $wsServerPort = (int)$_SERVER['SWOOLE_WS_SERVER_PORT'] ?: 8004;
        $wsServerPort = $port ? : $wsServerPort;
        $this->io->info('Starting WebSocket Server');

        $persistence = [
            new SocketChannelPersistenceTable(),
            new SocketListenerPersistenceTable(),
        ];

        if($enableSSL) {
            $server = new \Swoole\Http\Server(
                $wsServerHost,
                443,
                \OpenSwoole\Server::POOL_MODE, SWOOLE_SOCK_TCP | SWOOLE_SSL,
            );
        } else {
            $server = new Server($wsServerHost, $wsServerPort);
        }

        $server->on('start', function (Server $server) use ($wsServerHost, $wsServerPort, $http) {
            $this->io->success(
                sprintf('Swoole WebSocket Server is started at ws://%s:%d', $wsServerHost, $wsServerPort)
            );

            if($http) {
                $this->io->info('Starting HTTP Server');
            }
        });

        $server->on('message', function (Server $server, Frame $frame) use ($persistence) {
            $this->io->info('Received message: ' . $frame->data);
            $this->processMessage(
                $frame->data,
                $frame->fd,
                $server,
                $persistence,
            );

            //Broadcast to all clients
//            $this->io->info('Broadcasting message: ' . $frame->data);
//            foreach($server->connections as $fd) {
//                if(!$server->isEstablished($fd)) {
//                    continue;
//                }
//                $server->push($fd, json_encode([
//                    'user' => $this->userTable->get($fd, 'userName'),
//                    'message' => $frame->data,
//                ]));
//            }
        });

        $server->on('open', function (Server $server, Request $request) use ($persistence) {
//            if(
//                !isset($request->get['token'])
//                || !$this->identifyUser($request->get['token'], $request->fd)
//            ) {
//                $this->io->error('Invalid token, closing FD connection '. $request->fd. ' token: '.$request->get['token']);
//                $server->disconnect($request->fd, 1003, 'Invalid token');
//                return;
//            }

            $this->io->info('New connection: ' . $request->fd);
            $this->processMessage(
                json_encode(['action' => WelcomeAction::ACTION_NAME, 'data' =>[]]),
                $request->fd,
                $server,
                $persistence,
            );

            $this->processMessage(
                json_encode(['action' => NewConnectionAction::ACTION_NAME, 'data' =>[]]),
                $request->fd,
                $server,
                $persistence,
            );

            $this->io->info('User connected: ' . $request->fd);
        });

        $server->on('disconnect', function (Server $server, int $fd) {
            //Clear user data
            $this->io->info('User disconnected: ' . $fd);
            if ($this->userTable->exists($fd)) {
                $this->userTable->del($fd);
            }
        });

        $server->on('close', function (Server $server, int $fd) use ($persistence) {
            $this->io->info('User closed connection: ' . $fd);
            $this->processMessage(
                json_encode(['action' => ClosedConnectionAction::ACTION_NAME, 'data' =>[]]),
                $fd,
                $server,
                $persistence,
            );
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

        $serverParams = [
            'document_root' => ROOT_DIR . '/public',
            'enable_static_handler' => true,
            'static_handler_locations' => ['/js'],
        ];

        if($enableSSL) {
            $sslServerParams = [
                'ssl_cert_file' => ROOT_DIR . '/ssl/certificate.pem',
                'ssl_key_file' => ROOT_DIR . '/ssl/private_key.pem',
                'ssl_allow_self_signed' => true,
                'ssl_verify_peer' => false,
                'open_http_protocol' => true,
            ];
            $serverParams = array_merge($serverParams, $sslServerParams);
        }

        $server->set($serverParams);
        $server->start();

    }

    private function processMessage(string $data, int $fd, Server $server, array $persistence) {
        $this->io->info('Processing message: ' . $data);
        $actions = [
            WelcomeAction::class,
            SecondaryBroadcastAction::class,
            NewConnectionAction::class,
            ClosedConnectionAction::class,
        ];
        $socketRouter = new SocketMessageRouter($persistence, $actions);
//        $actionManager = $socketRouter->getActionManager();
//        $actionManager->add(new WelcomeAction);
//        $actionManager->add(new SecondaryBroadcastAction);
//        $actionManager->add(new NewConnectionAction);
//        $actionManager->add(new ClosedConnectionAction);
        $socketRouter($data, $fd, $server);
//        public function __invoke(string $data, int $fd, mixed $server)
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