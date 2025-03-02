<?php

namespace App\Middleware;

use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as Handler;
use Slim\Psr7\Response as SlimResponse;
use Psr\Container\ContainerInterface;
use Slim\Routing\RouteParser;

class PublicRouteMiddleware implements MiddlewareInterface {
    private ContainerInterface $container;
    private RouteParser $router;

    public function __construct(ContainerInterface $container){
        $this->container = $container;
        $this->router = $container->get('router'); // Inject RouteParser
    }

    public function process(Request $request, Handler $handler): Response {
        if(session_status() === PHP_SESSION_NONE) session_start();
        
        // If user is already logged in and tries to access login, redirect to dashboard
        if(isset($_SESSION['user']) && $request->getUri()->getPath() === $this->router->urlFor('login')){
            $response = new SlimResponse();
            
            return $response
                ->withHeader('Location', $this->router->urlFor('dashboard'))
                ->withStatus(302);
        }

        return $handler->handle($request);
    }
}
