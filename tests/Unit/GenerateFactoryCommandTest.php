<?php

namespace Tests\Unit;

use Nekofar\Slim\Test\Traits\AppTestTrait;
use Tests\TestCase;

class GenerateFactoryCommandTest extends TestCase
{
    use AppTestTrait;

    protected function setUp(): void {
        $this->setUpApp($this->getApp());
    }

    public function test_can_generate_factory(): void {
        global $app;

        $this->mockFileSystem();
        $container = $app->getContainer();
        $filesystem = $container->get('filesystem');
        $model = 'User';
        $factoryPath = 'src/Models/Factories/' . $model . 'Factory.php';
        $modelPath = 'src/Models/' . $model . '.php';

        $gotStub = false;
        $filesystem
            ->shouldReceive('fileExists')
            ->andReturnUsing(function ($file) use (&$gotStub, $factoryPath, $modelPath) {
                if($modelPath === $file) {
                    return true;
                }

                if ($factoryPath === $file && !$gotStub) {
                    //If we get here, we have not yet generated the stub
                    return false;
                } else if ($factoryPath === $file && $gotStub) {
                    //If we get here, we have already generated the stub
                    return true;
                }

                $this->fail('fileExists was called with an unexpected file parameter');
            });

        $filesystem->shouldReceive('read')->andReturnUsing(function ($stub) use (&$gotStub) {
            if('src/Models/Factories/Factory.stub' === $stub) {
                $gotStub = true;
            }
            return 'Factory stub content {{model}}';
        });

        $filesystem->shouldReceive('write')->with($factoryPath, 'Factory stub content '. $model);
        $result = $this->runCommand(
            'generate:factory', [
                '--model' => $model
        ]);

        $result->assertCommandIsSuccessful();
        $this->assertTrue($gotStub);
    }

    public function test_fail_if_factory_already_exists(): void {
        global $app;

        $this->mockFileSystem();
        $container = $app->getContainer();
        $filesystem = $container->get('filesystem');
        $model = 'User';
        $factoryPath = 'src/Models/Factories/' . $model . 'Factory.php';
        $modelPath = 'src/Models/' . $model . '.php';

        $filesystem
            ->shouldReceive('fileExists')
            ->andReturnUsing(function ($file) use (&$gotStub, $factoryPath, $modelPath) {
                if($modelPath === $file) {
                    return true;
                }

                if ($factoryPath === $file) {
                    return true;
                }

                $this->fail('fileExists was called with an unexpected file parameter');
            });

        $result = $this->runCommand(':generate:factory', [
            '--model' => $model
        ]);

        $this->assertStringContainsString('ERROR', $result->getDisplay());

    }
}