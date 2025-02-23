<?php

namespace App\Middleware;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface as Handler;
use Slim\Exception\HttpUnauthorizedException;

class SessionAuthMiddleware implements MiddlewareInterface {
    
    public function process(Request $request, Handler $handler): Response {
        if(!isset($_SESSION['user'])){
            throw new HttpUnauthorizedException($request, 'Unauthorized - Please log in');
        }

        return $handler->handle($request);
    }
}