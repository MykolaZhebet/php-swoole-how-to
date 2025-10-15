<?php

namespace App\Controllers;

use App\Models\User;
use League\Plates\Engine;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\RequestInterface;
class HomeController
{
    public function __construct(
        protected ContainerInterface $container,
    ) {}
    public function welcome(RequestInterface $request, ResponseInterface $response, $args) {
        $templates = new Engine(ROOT_DIR . '/src/Views');
        $response->getBody()->write($templates->render('welcome', ['testVar' => 'Hello World!']));
        return $response;
    }

    public function showUser(RequestInterface $request, ResponseInterface $response, $args) {
        $templates = new Engine(ROOT_DIR . '/src/Views');
        $user = (new User)->find($args['id']);

        $logger = $this->container->get('logger');
        $logger->info('User found', ['user' => $user]);
        $logger->info(json_encode($request->session));

        $response->getBody()->write($templates->render('user', ['user' => current($user)]));
        return $response;
    }

    public function showUsers(RequestInterface $request, ResponseInterface $response, $args) {
        $templates = new Engine(ROOT_DIR . '/src/Views');
        $users = (new User)->getAll();
        $response->getBody()->write($templates->render('user', ['users' => $users]));
        return $response;
    }
}