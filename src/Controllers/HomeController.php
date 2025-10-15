<?php

namespace App\Controllers;

use App\Models\User;
use League\Plates\Engine;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\RequestInterface;
class HomeController
{
    public function welcome(RequestInterface $request, ResponseInterface $response, $args) {
        $templates = new Engine(ROOT_DIR . '/src/Views');
        $response->getBody()->write($templates->render('welcome', ['testVar' => 'Hello World!']));
        return $response;
    }

    public function showUser(RequestInterface $request, ResponseInterface $response, $args) {
        $templates = new Engine(ROOT_DIR . '/src/Views');
        $user = (new User)->find($args['id']);
        $response->getBody()->write($templates->render('user', ['user' => current($user)]));
        return $response;
    }
}