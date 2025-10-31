<?php

use App\Application\Middlewares\JwtAuthMiddleWare;
use App\Controllers\Api\UserController;
use Slim\Routing\RouteCollectorProxy;

return function (RouteCollectorProxy $group) {
    $group->group('', function (RouteCollectorProxy $group2) {
       $group2->get('/users', UserController::class.':index')->setName('users-api-get');
       $group2->post('/users', UserController::class.':create')->setName('users-api-create');
       $group2->put('/users/{user_id}', UserController::class.':update')->setName('users-api-update');
       $group2->delete('/users/{user_id}', UserController::class.':delete')->setName('users-api-delete');
    })->add(new JwtAuthMiddleWare());
};
