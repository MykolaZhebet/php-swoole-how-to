<?php

namespace App\Application\Middlewares;

use App\Services\JwtToken;
use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;


class JwtAuthMiddleWare
{

    public function __invoke(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface {
        $auth = JwtToken::getToken($request);

        if(empty($auth)) {
            $factory = new Psr17Factory();
            $stream = $factory->createStream(
                json_encode([
                    'status' => 'unathorized',
                    'message' => 'Unauthorized request'
            ]));

            return (new Response())
                ->withBody($stream)
                ->withStatus(401)
                ->withHeader('Content-Type', 'application/json');
        }
        return $handler->handle($request);
    }
}