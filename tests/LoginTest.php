<?php

namespace Tests;

use App\Models\User;
use Nekofar\Slim\Test\Traits\AppTestTrait;
use Nekofar\Slim\Test\TestResponse;
use Psr\Http\Message\RequestInterface;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use AppTestTrait;
    private array $cookies = [];

    protected function setUp(): void {
        $this->setUpApp($this->getApp());
    }


    private function login(): TestResponse {
        return $this->post('/api/login', [
            'email' => 'test@testdomain.com',
            'password' => 'test',
        ]);
    }

    private function send($request, array $headers): TestResponse {
        if(!empty($this->cookies)) {
            $request = $request->withCookieParams($this->cookies);
        }

        if (!is_null($this->defaultHeaders)) {
            $headers = array_merge($this->defaultHeaders, $headers);
        }

        foreach($headers as $key => $value) {
            $request = $request->withHeader($key, $value);
        }

        return TestResponse::fromBaseResponse($this->app->handle($request));
    }

    public function test_can_login(): void {
        $response = $this->login();
        $response->assertStatus(302);
        $response->assertHeader('Location', '/admin');
        $session = $this->getSessionCookieFromResponse($response);
        $this->assertArrayHasKey('user_id', $session);
    }

    public function test_can_login_with_wrong_creds(): void {
        $response = $this->post('/login', [
            'email' => 'test@test.test',
            'password' => 'wrong',
        ]);
        $response->assertStatus(302);
        $redirectUrl = $response->getHeader('Location');
        $parsedUrl = parse_url($redirectUrl);
        parse_str($parsedUrl['query'], $parsedUrl['query']);
        $this->assertEquals('/login', $parsedUrl['path']);
        $this->assertEquals('Failed to authenticate', $parsedUrl['query']['error']);

        $session = $this->getSessionCookieFromResponse($response);
        $this->assertArrayNotHasKey('user_id', $session);
    }

    public function test_can_logout(): void {
        $cookieKey = 'Set-Cookie';

        $response = $this->login();
        $response->assertStatus(302);
        $session = $this->getSessionCookieFromResponse($response);
        $this->assertArrayHasKey('user_id', $session);
        $this->cookies = $this->getCookieParams($response);

        $response = $this->get('/admin');
        $response->assertOk();

        $session = $this->getSessionCookieFromResponse($response);
        $this->assertArrayHasKey('user_id', $session);

        $response = $this->post('/logout');
        $response->assertOk();

        $this->getSessionCookieFromResponse($response);
        $this->assertArrayNotHasKey('user_id', $session);




    }



}