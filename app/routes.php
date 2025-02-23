<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;
use Slim\Routing\RouteCollectorProxy;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use App\Middleware\TokenAuthMiddleware;
use App\Middleware\SessionAuthMiddleware;

return function (App $app){

    $container = $app->getContainer();
    $jwtConfig = $container->get('settings')['jwt'];

    // 🔓 Public Facing No Authentication Routes (Grouped Under '')
    $app->group('/', function(RouteCollectorProxy $group) use ($container, $jwtConfig){
        $group->get('test-db', function ($request, $response) {
            $db = $this->get('db');
            $stmt = $db->query('SELECT name FROM sys.databases');
            $databases = $stmt->fetchAll();
            return $response->withJson($databases);
        });

        $group->group('test', function(RouteCollectorProxy $group){
            $group->get('', 'App\Controllers\HomeController:getPage');
        });



    });

    // 🔒 Protected Web Routes (Grouped Under '/')
    $app->group('', function(RouteCollectorProxy $group){
        $group->get('/dashboard', function(Request $request, Response $response){
            return $response->getBody()->write("Welcome, {$_SESSION['user']}! <a href='/logout'>Logout</a>");
        });

        // Admin-only Web Routes (Inside Protected Group)
        $group->group('/admin', function(RouteCollectorProxy $adminGroup){
            $adminGroup->get('/users', function(Request $request, Response $response){
                return $response->getBody()->write("Admin Users Page for {$_SESSION['user']}");
            });

            $adminGroup->get('/settings', function(Request $request, Response $response){
                return $response->getBody()->write("Admin Settings for {$_SESSION['user']}");
            });
        }); // ->add(); Apply extra check for admin if needed

    })->add(new SessionAuthMiddleware());


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


/*
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;
use Slim\Routing\RouteCollectorProxy;
use Slim\Views\PhpRenderer;
use Firebase\JWT\JWT;
use App\Middleware\SessionAuthMiddleware;
use App\Middleware\JWTAuthMiddleware;

return function (App $app) {
    $container = $app->getContainer();
    $jwtConfig = $container->get('settings')['jwt'];

    // 🔓 Public Authentication Routes (Grouped Under '')
    $app->group('', function (RouteCollectorProxy $group) use ($container, $jwtConfig) {
        // Login Page (Web)
        $group->get('/login', function (Request $request, Response $response) {
            $renderer = new PhpRenderer(__DIR__ . '/../views');
            return $renderer->render($response, "login.php");
        });

        // Process Login (Session for Web, JWT for API)
        $group->post('/login', function (Request $request, Response $response) use ($container, $jwtConfig) {
            $data = $request->getParsedBody();
            $username = $data['username'] ?? '';
            $password = $data['password'] ?? '';
            $userAgent = $request->getHeaderLine('User-Agent');

            if ($username === 'admin' && $password === 'password') {
                if (strpos($userAgent, 'Mozilla') !== false) {
                    // Web Login (Session)
                    $_SESSION['user'] = $username;
                    $_SESSION['role'] = 'admin'; // Store user role
                    return $response->withHeader('Location', '/dashboard')->withStatus(302);
                } else {
                    // API Login (JWT)
                    $payload = [
                        'iss' => $jwtConfig['issuer'],
                        'iat' => time(),
                        'exp' => time() + $jwtConfig['exp'],
                        'sub' => $username,
                        'role' => 'admin' // Include role in JWT
                    ];
                    $jwt = JWT::encode($payload, $jwtConfig['secret'], 'HS256');

                    return $response->write(json_encode(['token' => $jwt]));
                }
            }

            return $response->withStatus(401)->write(json_encode(['message' => 'Invalid credentials']));
        });

        // Logout Route (For Web Users)
        $group->get('/logout', function (Request $request, Response $response) {
            session_destroy();
            return $response->withHeader('Location', '/login')->withStatus(302);
        });
    });

    // 🔒 Protected Web Routes (Grouped Under '/')
    $app->group('/', function (RouteCollectorProxy $group) {
        $group->get('dashboard', function (Request $request, Response $response) {
            return $response->write("Welcome, {$_SESSION['user']}! <a href='/logout'>Logout</a>");
        });

        // ✅ Nested: Admin-only Web Routes (Inside Protected Group)
        $group->group('admin', function (RouteCollectorProxy $adminGroup) {
            $adminGroup->get('/users', function (Request $request, Response $response) {
                return $response->write("Admin Users Page for {$_SESSION['user']}");
            });

            $adminGroup->get('/settings', function (Request $request, Response $response) {
                return $response->write("Admin Settings for {$_SESSION['user']}");
            });
        })->add(new SessionAuthMiddleware()); // Apply extra check for admin if needed
    })->add(new SessionAuthMiddleware());

    // 🔒 Protected API Routes (Grouped Under '/api')
    $app->group('/api', function (RouteCollectorProxy $group) {
        $group->get('/dashboard', function (Request $request, Response $response) {
            $user = $request->getAttribute('user');
            return $response->write(json_encode(['message' => "API Dashboard for {$user->sub}!"]));
        });

        // ✅ Nested: Admin-only API Routes (Inside Protected Group)
        $group->group('/admin', function (RouteCollectorProxy $adminGroup) {
            $adminGroup->get('/users', function (Request $request, Response $response) {
                $user = $request->getAttribute('user');
                return $response->write(json_encode(['message' => "Admin API - Users for {$user->sub}!"]));
            });

            $adminGroup->get('/settings', function (Request $request, Response $response) {
                $user = $request->getAttribute('user');
                return $response->write(json_encode(['message' => "Admin API - Settings for {$user->sub}!"]));
            });
        })->add(new JWTAuthMiddleware($container));
    })->add(new JWTAuthMiddleware($container));
};
*/