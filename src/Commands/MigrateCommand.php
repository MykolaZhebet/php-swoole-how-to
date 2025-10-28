<?php

namespace App\Commands;

use App\Infrastructure\Migration;
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
use Slim\App;
class MigrateCommand extends Command
{
    protected static $defaultName = 'migration:migrate';
    protected static $defaultDescription = 'Table migration';

    protected function configure() {
        $this
            ->setName(self::$defaultName)
            ->setDescription(self::$defaultDescription)
            ->setDefinition(
                new InputDefinition([
                    new InputOption('fresh', null, InputOption::VALUE_OPTIONAL, 'Is need to run migration from beginning', false),
                ])

            )
            ->setHelp(self::$defaultDescription);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int {
        /** @var App $app */
        global $app;
        $io = new SymfonyStyle($input, $output);
        $isFresh = $input->getOption('fresh');
        Migration::handle($app, $isFresh);
        return Command::SUCCESS;
    }
}