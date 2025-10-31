<?php

namespace Tests;

use App\Models\User;
use Tests\Traits\SwooleAppTestTrait;

class ApiUserTest extends TestCase
{
    use SwooleAppTestTrait;

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

    private function generateToken(): string {
        $user = User::find(1);

        $this->runCommand('jwt-token:generate', [
            '--name' => 'token-name',
            '--user' => $user->email,
            '--quiet' => null,
        ]);

        $user->refresh();
        return $user->tokens->first()->token;
    }
    public function test_can_create_user(): void {
        $token = $this->generateToken();

        $userData = User::factory()->make();

        $this->assertCount(0, User::where('email', $userData->email)->get());

        $response = $this
            ->withHeaders([
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
                'Authorization' => 'Bearer ' . $token
            ])
            ->PostJson('/api/users', $userData->toArray());

        $response->assertStatus(201);
        $response->assertJson([
            'data' => [
                'name' => $userData->name,
                'email' => $userData->email,
            ]
        ]);
        $this->assertCount(1, User::where('email', $userData->email)->get());
    }

    public function test_cant_create_user_without_creds(): void {

        $userData = User::factory()->make();

        $this->assertCount(0, User::where('email', $userData->email)->get());

        $response = $this
            ->withHeaders([
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])
            ->PostJson('/api/users', $userData->toArray());

        $response->assertStatus(401);

        $this->assertCount(0, User::where('email', $userData->email)->get());
    }

    public function test_can_update_user(): void {
        $token = $this->generateToken();

        $userData = User::factory()->create();
        $this->assertCount(1,
            User::where('email', $userData->email)
                ->where('name', $userData->name)
                ->get()
        );

        $newData = User::factory()->make();
        $response = $this
            ->withHeaders([
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
                'Authorization' => 'Bearer ' . $token
            ])
            ->PutJson('/api/users', ['name' => $newData->name]);

        $body = $response->getBody();
        $body->rewind();
        $response->assertStatus(200);
        $response->assertJson([
            'data' => [
                'name' => $newData->name,
                'email' => $userData->email,
            ]
        ]);
        $this->assertCount(0, User::where('email', $userData->email)->where('name', $userData->name)->get());
        $this->assertCount(1, User::where('email', $userData->email)->where('name', $newData->name)->get());
    }
    public function test_can_delete_user(): void {
        $token = $this->generateToken();
        $userData = User::factory()->create();
        $this->assertCount(1,
            User::where('email', $userData->email)
                ->where('name', $userData->name)
                ->get()
        );

        $response = $this
            ->withHeaders([
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
                'Authorization' => 'Bearer ' . $token
            ])
            ->delete('/api/users' . $userData->id);

        $this->assertCount(0, User::where('email', $userData->email)->where('name', $userData->name)->get());
    }
    public function test_cant_delete_user_without_creds(): void {
        $userData = User::factory()->create();
        $this->assertCount(1,
            User::where('email', $userData->email)
                ->where('name', $userData->name)
                ->get()
        );

        $response = $this
            ->withHeaders([
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])
            ->delete('/api/users' . $userData->id);
        $response->assertStatus(401);
        $this->assertCount(1, User::where('email', $userData->email)->where('name', $userData->name)->get());
    }



}