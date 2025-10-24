<?php
namespace App\Controllers;

use App\Infrastructure\Services\SessionTable;
use App\Models\User;
use League\Plates\Engine;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class LoginController {

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
}
