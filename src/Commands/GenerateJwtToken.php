<?php

namespace App\Commands;

use App\Models\Token;
use App\Models\User;
use App\Services\JwToken;
use Carbon\Carbon;
use Firebase\JWT\JWT;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class GenerateJwtToken extends Command
{
    protected static $defaultName = 'jwt-token:generate';
    protected static $defaultDescription = 'Generates Jwt token for a user';

    protected function configure() {
        $this
            ->setName(self::$defaultName)
            ->setDescription(self::$defaultDescription)
            ->setDefinition(
                new InputDefinition([
                    new InputOption('name', null, InputOption::VALUE_REQUIRED, 'The name of JWT token'),
                    new InputOption('email', null, InputOption::VALUE_REQUIRED, 'The email of the user that JWT token is for'),
                    new InputOption('expire', null, InputOption::VALUE_OPTIONAL, 'The number of seconds until the token expiration.'),
                ])

            )
            ->setHelp(self::$defaultDescription);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int {
        $io = new SymfonyStyle($input, $output);

        try {
            $this->generateToken($input, $io);
        } catch(\Exception $e) {
            if(!$input->getOption('quiet')) {
                $io->error(sprintf('There was an error: %s', $e->getMessage()));
            }
            return Command::FAILURE;
        }
        return Command::SUCCESS;
    }

    private function generateToken(InputInterface $input, SymfonyStyle $io) {
        $name = $input->getOption('name');
        $userEmail = $input->getOption('email');
        $expire = $input->getOption('expire');

        $user = User::where('email', $userEmail)->first();


        //@see https://datatracker.ietf.org/doc/html/rfc7519
        $payload = [
            'iat' => Carbon::now()->timestamp, //Issued At
            'user_id' => $user->id,
        ];

        if($expire) {
            $expire = Carbon::now()->addSeconds($expire);
            $payload['exp'] =  $expire->timestamp; //Expiration Time
        }


        $token = JWT::encode($payload, $name, JwToken::HS256_ALGORITHM);

        $tokenRecord = Token::create([
            'name' => $name,
            'user_id' => $user->id,
            'expire_at' => $expire ? $expire->format('Y-m-d H:i:s'): null,
            'token' => $token,
        ]);

        if(!$input->getOption('quiet')) {
            $io->success('Token generated successfully: ' . $token);
        }

    }

}