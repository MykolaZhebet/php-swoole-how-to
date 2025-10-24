<?php
use \Slim\App;

use App\Application\Middlewares\AuthorizationMiddleWare;
use App\Controllers\HomeController;
use Slim\Routing\RouteCollectorProxy;
use App\Application\Middlewares\SessionMiddleware;
use App\Application\Middlewares\CheckUsersExistenceMiddleware;

return function (App $app) {
    $app->group('',function(RouteCollectorProxy $group) {
        $group->get('/', HomeController::class . ':welcome');
        $group->get('/login', HomeController::class . ':login' )
            ->setName('login')
            ->add(new AuthorizationMiddleWare());
        $group->post('/login', HomeController::class . ':loginHandler' )
            ->setName('login-handler')
            ->add(new AuthorizationMiddleWare());
        $group->post('/logout', HomeController::class . ':logoutHandler' )
            ->setName('logout-handler')
            ->add(new AuthorizationMiddleWare());
        $group->get('/admin', HomeController::class . ':admin' )
            ->setName('admin')
            ->add(new AuthorizationMiddleWare());

        $group->get('/users', HomeController::class . ':showUsers');
        $group->get('/users/{id:[0-9]+}', HomeController::class . ':showUser')->add(new CheckUsersExistenceMiddleware());
    })->add(new SessionMiddleware());
};
