<?php

namespace App\Commands;

use App\Application\Rules\RecordExist;
use App\Models\Token;
use App\Models\User;
use App\Services\JwtToken;
use App\Services\Validator;
use Carbon\Carbon;
use Firebase\JWT\JWT;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Type;

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
                    new InputOption('useLimit', null, InputOption::VALUE_OPTIONAL, 'The number of times this token can be used.'),
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
        [$name, $userEmail, $expire, $useLimit] = $this->validateInput($input, $io);
        $user = User::where('email', $userEmail)->first();

        $tokenRecord = JwtToken::create(
            $name,
            $user->id,
            $expire,
            $useLimit,
        );

        if(!$input->getOption('quiet')) {
            $io->success('Token generated successfully: ' . $tokenRecord);
        }
    }

    private function validateInput(InputInterface $input, SymfonyStyle $io): array  {
        $name = $input->getOption('name');
        $userEmail = $input->getOption('email');
        $expire = $input->getOption('expire');
        $useLimit = $input->getOption('useLimit');
        /** @Throws \Exception */
        Validator::validate([
            'name' => $name,
            'userEmail' => $userEmail,
            'expire' => $expire,
            'useLimit' => $useLimit,
        ], [
            'name' => [
                new NotBlank(null, 'Token name is required'),
                new Type('string', 'Token name must be a string')
            ],
            'userEmail' => [
                new NotBlank(null, 'User email is required'),
                new Type('string', 'User email must be a string'),
                new Email(null, 'User email must be a valid email'),
                new RecordExist([
                    'model' => User::class,
                    'field' => 'email',
                ], 'Email doesn\'t exist')
            ],
            'expire' => [
                new Type('integer', 'Expire must be an inteber')
            ],
            'useLimit' => [
                new Type('integer', 'UseLimit must be an inteber')
            ]
        ]);

        return [$name, $userEmail, $expire, $useLimit];
    }

}