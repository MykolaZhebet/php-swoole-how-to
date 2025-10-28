<?php

namespace App\Controllers\Api;

use App\Models\User;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class UserController
{
    public function index(RequestInterface $request, ResponseInterface $response, $args) {
        $data = User::all()->map(function(User $user) {
            $userData = $user->toArray();
            unset($userData['password']);
            unset($userData['created_at']);
            unset($userData['updated_at']);
            return $userData;
        });
        $response->getBody()->write(json_encode($data));
        return $response;
    }
}