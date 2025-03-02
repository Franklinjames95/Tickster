<?php

namespace App\Middleware;

use Slim\App;
use Slim\Routing\RouteParser;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Config\CustomBlade as Blade;
use Psr\Log\LoggerInterface;
use Throwable;

class ErrorHandlerMiddleware {
    
    private App $app;
    private Blade $blade;
    private LoggerInterface $logger;
    private RouteParser $router;

    public function __construct(App $app) {
        $this->app = $app;
        
        $container = $app->getContainer();
        $this->blade = $container->get('blade');
        $this->logger = $container->get(LoggerInterface::class); // ✅ Use main app logger
        $this->router = $container->get('router');
    }   

    public function __invoke(
        Request $request,
        Throwable $exception,
        bool $displayErrorDetails,
        bool $logErrors,
        bool $logErrorDetails
    ): Response {
        $response = $this->app->getResponseFactory()->createResponse();

        // Start session if not already started
        if (session_status() === PHP_SESSION_NONE) session_start();

        // Check if user is authenticated
        $isAuthenticated = isset($_SESSION['user']);

        // If it's a 404 error and the user is NOT authenticated, redirect dynamically to login
        if (
            $exception instanceof \Slim\Exception\HttpNotFoundException 
            && !$isAuthenticated
            && $request->getUri()->getPath() !== $this->router->urlFor('login')
        ) {
            return $response
                ->withHeader('Location', $this->router->urlFor('login'))
                ->withStatus(302);
        }

        // Exception-to-template map
        $exceptionMap = [
            'Slim\Exception\HttpNotFoundException' => ['exceptions.404', 404],
            'Slim\Exception\HttpUnauthorizedException' => ['exceptions.401', 401],
            'Slim\Exception\HttpForbiddenException' => ['exceptions.403', 403],
            'Slim\Exception\HttpInternalServerErrorException' => ['exceptions.500', 500],
        ];

        // Determine status and template
        $exceptionClass = get_class($exception);
        [$template, $statusCode] = $exceptionMap[$exceptionClass] ?? ['exceptions.500', 500];

        // Log error details using app.log (NOT sql.log)
        $logData = [
            'status_code' => $statusCode,
            'message' => $exception->getMessage(),
            'exception' => $exception,
            'url' => (string) $request->getUri(),
            'method' => $request->getMethod(),
            'ip' => $request->getServerParams()['REMOTE_ADDR'] ?? 'Unknown',
            'user_agent' => $request->getHeaderLine('User-Agent') ?: 'Unknown',
            'session_user' => $_SESSION['user'] ?? 'Guest',
            'trace' => $exception->getTraceAsString(),
        ];

        $this->logger->error("[$statusCode] {$exception->getMessage()}", $logData);

        // Render error page
        $html = $this->blade->run($template, [
            'title' => "Error $statusCode",
            'message' => $exception->getMessage(),
        ]);

        $response->getBody()->write($html);
        return $response->withStatus($statusCode);
    }
}
