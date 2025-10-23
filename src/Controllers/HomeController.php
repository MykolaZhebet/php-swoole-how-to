<?php

namespace App\Controllers;

use App\Infrastructure\Services\SessionTable;
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

    public function login(RequestInterface $request, ResponseInterface $response, $args) {
        $templates = new Engine(ROOT_DIR . '/src/Views');
        $response->getBody()->write($templates->render('login'));
        return $response;
    }

    public function loginhandler(RequestInterface $request, ResponseInterface $response, $args) {
        global $app;

        $data = $request->getParsedBody();
        //Todo: validation
        $user = current((new User)->get('email', $data['email']));
        //Todo: verify if the user was found
        if (!password_verify($data['password'], $user['password'])) {
            $app->getContainer()->get('logger')->info('Wrong password!');
            return $response
                ->withHeader('Location', '/login?error=Failed to authenticate')
                ->withStatus(302);
        }

        $sessionTable = SessionTable::getInstance();
        $sessionTable->set($request->session['id'], [
            'id' => $request->session['id'],
            'user_id' => $user['id'],
        ]);

        return $response
            ->withHeader('Location', '/admin')
            ->withStatus(302);
    }

    public function logoutHandler(RequestInterface $request, ResponseInterface $response, $args) {
        //Todo validation
        $sessionTable = SessionTable::getInstance();
        $sessionTable->set($request->session['id'], [
            'id' => $request->session['id']
        ]);

        return $response
            ->withHeader('Location', '/login')
            ->withStatus(302);
    }

    public function admin(RequestInterface $request, ResponseInterface $response, $args) {
        $sessionTable = SessionTable::getInstance();
        $sessionData = $sessionTable->get($request->session['id']);
        $user = current((new User)->find($sessionData['user_id']));

        $templates = new Engine(ROOT_DIR . '/src/Views');
        $response->getBody()->write($templates->render('admin', ['userName' => $user['name']]));
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