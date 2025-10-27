<?php
namespace App\Bootstrap;

use App\Events\EventInterface;
use App\Events\EventLogin;
use App\Infrastructure\Migration;
use App\Infrastructure\Seed;
use App\Models\User;
use App\Services\Event;
use Nyholm\Psr7\Factory\Psr17Factory;
use Ilex\SwoolePsr7\SwooleServerRequestConverter;
use Slim\App as SlimApp;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\ConsoleOutput;

class App  {
    public static function start(): void {
        [$app, $requestConverter] = App::prepareSlimApp();

        include_once ROOT_DIR . '/src/constants.php';

        Dependencies::start($app);

        self::registerEvents($app);

        (require ROOT_DIR . '/src/routes.php')($app);

        if(self::processCommands($app)) {
            echo 'Command executed successfully'. PHP_EOL;
            return;
        }

        SwooleServer::start($app, $requestConverter);
    }

    /**
     * @return array{ 0: SlimApp, 1: SwooleServerRequestConverter }
     */
    private static function prepareSlimApp(): array {
        global $app;
        $psr17Factory = new Psr17Factory();

        $requestConverter = new SwooleServerRequestConverter(
            $psr17Factory, $psr17Factory, $psr17Factory, $psr17Factory
        );

        $app = new SlimApp($psr17Factory, new \DI\Container());
        $app->addRoutingMiddleware();
        $app->addErrorMiddleware(true, true, true);

        return [$app, $requestConverter];
    }

    private static function processCommands(SlimApp $app): bool {
        $input = self::getConsoleInput();
        $output =  new ConsoleOutput();
        switch($input->getArgument('action')) {
            case 'migrate':
                $output->writeln('Migration started');
                Migration::handle($app, $input );
                return true;
            break;
            case 'seed':
                $output->writeln('Seeding started');
                Seed::handle($app);
                return true;
            default:
                return false;
        }
    }
    
    private static function getConsoleInput(): InputInterface {
        global $argv;

        $output =  new ConsoleOutput();

        $definition = new InputDefinition([
            new InputArgument(
                name: 'action',
                mode: InputArgument::OPTIONAL,
                description: 'Action to be taken'
            ),
            new InputOption(
                name: 'fresh',
                shortcut: null,
                mode: InputOption::VALUE_NONE,
                description: 'Make migration running fresh',
                default: null
            )
        ]);

        try {
            return new ArgvInput($argv, $definition);
        } catch(\Exception $e) {
            $output->writeln('');
            $output->writeln('<error>There was an error during the star: ' . $e->getMessage() . '</error>');
            $output->writeln('');
            exit(1);
        }
    }

    private static function registerEvents(SlimApp $app): void {
        $container = $app->getContainer();
//        (Event::getInstance())->addListener(LOGIN_EVENT, function(string $data) use ($container) {

        (Event::getInstance())->addListener(EventLogin::class, function(EventInterface $event) use ($container) {
            $logger = $container->get('logger');
            /** @var EventLogin $event */
//            $user = User::find((int)$parsedData['user_id']);
            $logger->info('User ' . $event->user->name . ' logged in');
        });

    }
}