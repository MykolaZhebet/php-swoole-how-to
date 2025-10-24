<?php
namespace App\Application\Middlewares;

use App\Infrastructure\Services\SessionTable;
use Nyholm\Psr7\Response;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Routing\RouteContext;

class AuthorizationMiddleWare {
    public function __invoke(ServerRequestInterface $request, RequestHandlerInterface $handler) {
        $routeContext = RouteContext::FromRequest($request);
        $route = $routeContext->getRoute();
        $isLoginRoute = in_array($route->getName(), ['login', 'login-handler']);
        $isAdminRoute = in_array($route->getName(), ['admin', 'admin']);
        $sessionTable = SessionTable::getInstance();
        $sessionData = $sessionTable->get($request->session['id']);

        if(!$isLoginRoute && !isset($sessionData['user_id'])) {
            return (new Response)
                ->withHeader('Location', '/login')
                ->withStatus(302);
        }

        if($isLoginRoute && isset($sessionData['user_id'])) {
            error_log('redirect to Admin route from Slim!!');
            return (new Response)
                ->withHeader('Location', '/admin')
                ->withStatus(302);
        }
        return $handler->handle($request);
    }
}