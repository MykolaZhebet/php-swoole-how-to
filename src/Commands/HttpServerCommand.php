<?php

namespace App\Commands;

use Ilex\SwoolePsr7\SwooleResponseConverter;
use Swoole\Http\Request;
use Swoole\Http\Response;
use Swoole\Http\Server;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class HttpServerCommand extends Command
{
    protected static $defaultName = 'http-server';
    protected static $defaultDescription = 'Run http server';
    private $isEnabledSSL = false;

    protected function configure() {
        $this
            ->setName(self::$defaultName)
            ->setDescription(self::$defaultDescription)
            ->setDefinition(
                new InputDefinition([
                    new InputOption('enableSSL', null, InputOption::VALUE_OPTIONAL, 'Enable SSL', false),
                ])
            )
            ->setHelp(self::$defaultDescription);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int {
        $io = new SymfonyStyle($input, $output);
        $this->isEnabledSSL = $input->getOption('enableSSL');
        $this->start($io);
        return Command::SUCCESS;
    }

    protected function start(SymfonyStyle $io) {
        global $app, $requestConverter;
        $serverHost = (string)$_SERVER['SWOOLE_SERVER_HOST'] ?: '0.0.0.0';
        $serverPort = (int)$_SERVER['SWOOLE_SERVER_PORT'] ?: 8003;

        if($this->isEnabledSSL) {
            $server = new Server(
                $serverHost,
                443,
                \OpenSwoole\Server::POOL_MODE, SWOOLE_SOCK_TCP | SWOOLE_SSL,
            );
        } else {
            $server = new Server($serverHost, $serverPort);
        }

        $io->info('Starting HTTPs Server');

        $server->on('start', function (Server $server) use ($serverHost, $serverPort, $io) {
            $io->success(sprintf(
                "swoole http server is started at %s:%d",
                $serverHost,
                $serverPort
            ));
        });

        $server->on('request', function (Request $request, Response $response) use ($app, $requestConverter) {
            $psr7Request = $requestConverter->createFromSwoole($request);
            $psr7Response = $app->handle($psr7Request);
            $converter = new SwooleResponseConverter($response);
            $converter->send($psr7Response);
        });

        $serverParams = [
            'document_root' => ROOT_DIR . '/public',
            'enable_static_handler' => true,
            'static_handler_locations' => ['/js'],
        ];

        if($this->isEnabledSSL) {
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

}