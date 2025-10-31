<?php

namespace App\Services;

use Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactory;
use Symfony\Component\PasswordHasher\PasswordHasherInterface;

class Hash
{

    protected PasswordHasherInterface $passwordHasher;

    public function __construct() {
        $factory = new PasswordHasherFactory([
            'common' => ['algorithm' => 'bcrypt'],
            'memory-hard' => ['algorithm' => 'sodium']
        ]);

        $this->passwordHasher = $factory->getPasswordHasher('common');
    }

    public function make(string $password): string {
        return $this->passwordHasher->hash($password);
    }

    public function check(string $password, string $hash): bool {
        return $this->passwordHasher->verify($hash, $password);
    }
}