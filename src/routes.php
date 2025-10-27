<?php

use App\Controllers\AdminController;
use App\Controllers\LoginController;
use \Slim\App;

use App\Application\Middlewares\AuthorizationMiddleWare;
use App\Controllers\HomeController;
use Slim\Routing\RouteCollectorProxy;
use App\Application\Middlewares\SessionMiddleware;
use App\Application\Middlewares\CheckUsersExistenceMiddleware;

return function (App $app) {
    $app->group('',function(RouteCollectorProxy $group) {
        $group->get('/', HomeController::class . ':welcome');
        $group->get('/login', LoginController::class . ':login' )
            ->setName('login')
            ->add(new AuthorizationMiddleWare());
        $group->post('/login', LoginController::class . ':loginHandler' )
            ->setName('login-handler')
            ->add(new AuthorizationMiddleWare());
        $group->post('/logout', LoginController::class . ':logoutHandler' )
            ->setName('logout-handler')
            ->add(new AuthorizationMiddleWare());
        $group->get('/admin', AdminController::class . ':admin' )
            ->setName('admin')
            ->add(new AuthorizationMiddleWare());
    })->add(new SessionMiddleware());
};
