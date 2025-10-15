<?php
namespace App\Application\Middlewares;

use App\Models\User;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Routing\RouteContext;

class CheckUsersExistenceMiddleware {
    public function __invoke(ServerRequestInterface $request, RequestHandlerInterface $handler) {
        $routeContext = RouteContext::fromRequest($request);
        $route = $routeContext->getRoute();
        $id = $route->getArgument('id');
        $user = (new User)->find($id);
        if (!count($user)) {
            throw new \Exception('User not found');
        }

        $response = $handler->handle($request);
        return $response;
    }

}
