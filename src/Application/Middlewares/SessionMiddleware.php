<?php

namespace App\Application\Middlewares;

use App\Infrastructure\Services\Session;
use App\Models\User;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Routing\RouteContext;

class SessionMiddleware {
    public function __invoke(ServerRequestInterface $request, RequestHandlerInterface $handler) {
        $request->session = Session::startSession($request);
        error_log('Session started: '.\json_encode($request->session));
        $response = $handler->handle($request);
        Session::addCookiesToResponse($request, $response);
        return $response;
    }
}