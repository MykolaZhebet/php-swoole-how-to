<?php

namespace App\Infrastructure;

use App\Models\User;
use Slim\App;
use Symfony\Component\Console\Output\ConsoleOutput;

class Seed
{
    public static function handle(App $app) {
        self::seedUsers();
    }

    private static function seedUsers() {
        $output = new ConsoleOutput();

        try {
            $user = User::create([
                'name' => 'John',
                'email' => 'test@testdomain.com',
                'password' => password_hash('test', PASSWORD_DEFAULT)
            ]);
        } catch(\Exception $e) {
            $output->writeln('<error>Failed to insert user: '. $e->getMessage() .'</error>');
        }

        if ($user === null) {
            $output->writeln('<error>Failed to insert user</error>');
            return;
        }
        $output->writeln('<info>User inserted</info>');
    }
}