<?php
namespace App\Bootstrap;

use App\Commands\HttpServerCommand;
use App\Events\EventInterface;
use App\Events\EventLogin;
use App\Infrastructure\Migration;
use App\Infrastructure\Seed;
use App\Models\User;
use App\Services\Event;
use Nyholm\Psr7\Factory\Psr17Factory;
use Ilex\SwoolePsr7\SwooleServerRequestConverter;
use Slim\App as SlimApp;
use Slim\Routing\RouteCollectorProxy;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\ConsoleOutput;

class App  {
    public static function start(): void {
        $app = App::prepareSlimApp();

        include_once ROOT_DIR . '/src/constants.php';

        Dependencies::start($app);

        self::registerEvents($app);

        self::registerRoutes($app);

        self::processCommands($app);

//        SwooleServer::start($app, $requestConverter);
    }

    /**
     * @return array{ 0: SlimApp, 1: SwooleServerRequestConverter }
     */
    private static function prepareSlimApp(): SlimApp {
        global $app, $requestConverter;
        $psr17Factory = new Psr17Factory();

        $requestConverter = new SwooleServerRequestConverter(
            $psr17Factory, $psr17Factory, $psr17Factory, $psr17Factory
        );

        $app = new SlimApp($psr17Factory, new \DI\Container());
        $app->addRoutingMiddleware();
        $app->addErrorMiddleware(true, true, true);

        return $app;
    }

    private static function processCommands(SlimApp $app): void {
        $application = new Application();
        $application->add(new HttpServerCommand());
        $application->run();
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

    public static function registerRoutes(SlimApp $app): void {
        (require ROOT_DIR . '/src/routes.php')($app);

        $app->group('/api', function (RouteCollectorProxy $group) {
            (require ROOT_DIR . '/src/api-routes.php')($group);
        });
    }
}