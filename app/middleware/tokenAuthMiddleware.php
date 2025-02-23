<?php

namespace App\Middleware;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface as Handler;

class TokenAuthMiddleware implements MiddlewareInterface {

    private string $secretKey;

    public function __construct(ContainerInterface $container){
        $settings = $container->get('settings')['jwt'];
        $this->secretKey = $settings['secret'];
    }

    public function process(Request $request, Handler $handler): Response {
        $authHeader = $request->getHeaderLine('Authorization');

        if (!$authHeader || !preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
            $response = new \Slim\Psr7\Response();
            $response->getBody()->write(json_encode(['message' => 'Unauthorized']));
            return $response->withStatus(401)->withHeader('Content-Type', 'application/json');
        }

        $jwt = $matches[1];

        try {
            $decoded = JWT::decode($jwt, new Key($this->secretKey, 'HS256'));
            $request = $request->withAttribute('user', $decoded);
        } catch(\Exception $e){
            $response = new \Slim\Psr7\Response();
            $response->getBody()->write(json_encode(['message' => 'Invalid token']));
            return $response->withStatus(401)->withHeader('Content-Type', 'application/json');
        }

        return $handler->handle($request);
    }
}
