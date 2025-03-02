<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;
use Slim\Routing\RouteCollectorProxy;
use App\Middleware\TokenAuthMiddleware;
use App\Middleware\SessionAuthMiddleware;

return function(App $app){

    $container = $app->getContainer();

    $app->get('/debug-session', function ($request, $response) {
        if (session_status() === PHP_SESSION_NONE) session_start(); // Ensure session is started
    
        $response->getBody()->write("<pre>" . print_r($_SESSION, true) . "</pre>");
        return $response->withHeader('Content-Type', 'text/html');
    });
    

    // 🔓 Public Facing No Authentication Routes (Grouped Under '')
    $app->group('/', function(RouteCollectorProxy $group){

        $group->group('login', function(RouteCollectorProxy $group){
            $group->get('', 'App\Controllers\AuthController:getPage')->setName('login');
            $group->post('', 'App\Controllers\AuthController:login')->setName('login.post'); // Handle login submission
        });

    })->add($container->get('PublicRouteMiddleware'));

    
    // 🔒 Protected Web Routes (Grouped Under '/')
    $app->group('/', function(RouteCollectorProxy $group){

        $group->group('dashboard', function(RouteCollectorProxy $group){
            $group->get('', 'App\Controllers\DashboardController:getPage')->setName('dashboard');
        });       

    })->add($container->get('PermissionMiddleware'))->add(new SessionAuthMiddleware());


    // 🔒 Protected API Routes (Grouped Under '/api')
    $app->group('/api', function (RouteCollectorProxy $group) use ($container){
        $group->get('/dashboard', function (Request $request, Response $response){
            $user = $request->getAttribute('user');
            return $response->getBody()->write(json_encode(['message' => "API Dashboard for {$user->sub}!"]));
        });

        // Admin-only API Routes (Inside Protected Group)
        $group->group('/admin', function(RouteCollectorProxy $adminGroup){

        }); // ->add(); Apply extra check for admin if needed

    })->add(new TokenAuthMiddleware($container));

};