<?php

namespace App\Infrastructure;

use App\Models\Token;
use App\Models\User;
use Slim\App;
use Symfony\Component\Console\Output\ConsoleOutput;

class Seed
{
    public static function handle(App $app) {
        self::seedUsers();
        self::seedTokens();
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
    private static function seedTokens() {
        $output = new ConsoleOutput();

        try {
            $user = Token::create([
                'name' => 'token-name',
                'user_id' => 1,
                'token' => 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpYXQiOjE3NjE2NTM5NjEsInVzZXJfaWQiOjF9.kxExqJ2twqH5sX4xjmXMST75BHe91aZKt9Wk4Lt0jkg'
            ]);
        } catch(\Exception $e) {
            $output->writeln('<error>Failed to token: '. $e->getMessage() .'</error>');
        }

        if ($user === null) {
            $output->writeln('<error>Failed to insert token</error>');
            return;
        }
        $output->writeln('<info>Token inserted</info>');
    }
}