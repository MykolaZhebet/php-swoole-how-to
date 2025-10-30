<?php
namespace App\Commands;

use Mustache\Engine AS MustacheEngine;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class GenerateFactoryCommand extends Command {
    protected static $defaultName = 'generate:factory';
    protected static $defaultDescription = 'Generate factory';


    protected function configure() {
        $this->setName(self::$defaultName)
            ->setDescription(self::$defaultDescription)
            ->addOption(
                'model', 'm',
                InputOption::VALUE_REQUIRED, 'Model name',
                'The model for which the factory will be generated'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int {
        global $app;

        $io = new SymfonyStyle($input, $output);
        $model = $input->getOption('model');
        $filesystem = $app->getContainer()->get('filesystem');

        $modelPath = 'src/Models/' . $model . '.php';
        $factoryPath = 'src/Models/Factories/' . $model . 'Factory.php';
        $stubPath = 'src/Models/Factories/Factory.stub';
        if (!$filesystem->fileExists($modelPath)) {
            $io->error(sprintf(
                'Model %s does not exist',
                $model,
            ));
            return Command::FAILURE;
        }


        if($filesystem->fileExists('src/Models/Factories/' . $model . 'Factory.php')) {
            $io->error(sprintf(
                'Model %s factory already exists',
                $model,
            ));
            return Command::FAILURE;
        }

        $content = $filesystem->read($stubPath);
        $filesystem->write($factoryPath, $this->processStub($model, $content));

        $io->success('Factory created successfully at ' . $factoryPath);
        return Command::SUCCESS;
    }

    private function processStub(string $model, string $content): string {
        $mustache = new MustacheEngine([
            'entity_flags' => ENT_QUOTES,
        ]);

        return $mustache->render($content, ['model' => $model]);
    }
}