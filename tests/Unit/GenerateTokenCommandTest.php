<?php

namespace Tests\Unit;

use App\Models\User;
use Nekofar\Slim\Test\Traits\AppTestTrait;
use Tests\TestCase;


class GenerateTokenCommandTest extends TestCase
{
    use AppTestTrait;

    protected function setUp(): void {
        $this->setUpApp($this->getApp());
    }

    private function generateToken(array $args) {
        return $this->runCommand('jwt-token:generate', $args);
    }


    public function test_can_generate_token() {
        $user = User::find(1);

        $tester = $this->generateToken([
            '--name' => 'token-name',
            '--email' => $user->email,
        ]);

        $tester->assertCommandIsSuccessful();
        $this->assertStringContainsString(
            'Token generated successfully',
            $tester->getDisplay(),
            'Wrong output for generate token command with all options'
        );
    }

    public function test_can_generate_token_without_name() {
        $user = User::find(1);
        $tester = $this->generateToken([
            '--email' => $user->email,
        ]);

        $this->assertStringContainsString(
            'Token name is required',
            $tester->getDisplay(),
            'Wrong output for generate token command without name option'
        );
    }

    public function test_can_generate_token_without_user_email() {
        $user = User::find(1);
        $tester = $this->generateToken([
            '--name' => 'token-name',
        ]);

        $this->assertStringContainsString(
            'User email is required',
            $tester->getDisplay(),
            'Wrong output for generate token command without user email option'
        );
    }
}