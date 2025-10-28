<?php

namespace App\Infrastructure;

use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Slim\App;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\ConsoleOutput;

class Migration
{
    public static function handle(App $app, bool $isFresh): void {
        self::migrateUsers($app, $isFresh);
    }

    private static function migrateUsers(App $app, bool $fresh = false): void {
        $output = new ConsoleOutput();
        $user = new User();
        $db = $app->getContainer()->get('db')->schema();

        if ($db->hasTable($user->getTable())) {
            $output->writeln('Users table already exists');
            if ($fresh) {
                $output->writeln('Dropping users table(fresh flag)');
                $db->drop($user->getTable());
            } else {
                return;
            }
        }

        $db->create($user->getTable(), function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 40);
            $table->string('email', 40)->unique();
            $table->string('password', 150);
            $table->timestamps();
        });
        $output->writeln('Users table created');
    }
}