<?php

namespace Tests;

use App\Models\User;
use Nekofar\Slim\Test\Traits\AppTestTrait;

class ApiUserTest extends TestCase
{
    use AppTestTrait;

    protected function setUp(): void {
        $this->setUpApp($this->getApp());
    }

    public function test_can_get_users(): void {
        $user = User::find(1);
        $token = $user->tokens->first()->token;
        $response = $this
            ->withHeader('Authorization', 'Bearer ' . $token)
            ->get('/api/users');
        $response->assertOk();

        $bodyStream = $response->getBody();
        $bodyStream->rewind();
        $data = json_decode($bodyStream->getContents(), true);
        $firstUser = current($data);

        $this->assertIsArray($data);
        $this->AssertArrayHasKey('id', $firstUser);
        $this->AssertArrayHasKey('name', $firstUser);
        $this->AssertArrayHasKey('email', $firstUser);
    }



}