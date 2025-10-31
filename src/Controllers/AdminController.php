<?php
namespace App\Controllers;

use App\Infrastructure\Services\SessionTable;
use App\Models\User;
use App\Services\JwtToken;
use League\Plates\Engine;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class AdminController {
    public function admin(RequestInterface $request, ResponseInterface $response, $args) {
        global $app;
        $container = $app->getContainer();
        $sessionTable = SessionTable::getInstance();
        $sessionData = $sessionTable->get($request->session['id']);
        $user = User::find($sessionData['user_id']);
        $viewData = [
            'userName' => $user->name,
            'ws_context' => $container->has('ws-context')
                ? array_merge($container->get('ws-context'), [
                    'token' => JwtToken::create(
                        uniqid(),
                        $user->id,
                        null,
                        1
                    )->token,
                ])
                : null,
        ];
        $templates = new Engine(ROOT_DIR . '/src/Views');
        $response->getBody()->write($templates->render('admin', $viewData));
        return $response;
    }
}
