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


}