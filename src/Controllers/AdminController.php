<?php
namespace App\Controllers;

use App\Infrastructure\Services\SessionTable;
use App\Models\User;
use League\Plates\Engine;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class AdminController {
    public function admin(RequestInterface $request, ResponseInterface $response, $args) {
        $sessionTable = SessionTable::getInstance();
        $sessionData = $sessionTable->get($request->session['id']);
        $user = current((new User)->find($sessionData['user_id']));

        $templates = new Engine(ROOT_DIR . '/src/Views');
        $response->getBody()->write($templates->render('admin', ['userName' => $user['name']]));
        return $response;
    }
}
