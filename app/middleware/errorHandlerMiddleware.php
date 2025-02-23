<?php

namespace App\Middleware;

use Slim\App;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Exception\HttpException;
use App\Config\CustomBlade as Blade;
use Psr\Log\LoggerInterface;
use Throwable;

class ErrorHandlerMiddleware {
    
    private App $app;
    private Blade $blade;
    private LoggerInterface $logger;

    public function __construct(App $app){
        $this->app = $app;
        
        $container = $app->getContainer();
        $this->blade = $container->get('blade');
        $this->logger = $container->get(LoggerInterface::class);
    }   

    public function __invoke(
        Request $request,
        Throwable $exception,
        bool $displayErrorDetails,
        bool $logErrors,
        bool $logErrorDetails
    ): Response {
        $response = $this->app->getResponseFactory()->createResponse();

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

        // Special case for 503 errors (handled via HttpException)
        if($exception instanceof HttpException && $exception->getCode() === 503){
            [$template, $statusCode] = ['exceptions.503', 503];
        }

        // Enhanced Logging Information
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

        $html = $this->blade->run($template, [
            'title' => "Error $statusCode",
            'message' => $exception->getMessage(),
        ]);

        $response->getBody()->write($html);
        return $response->withStatus($statusCode);
    }
}
