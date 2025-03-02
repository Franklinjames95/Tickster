<?php

namespace App\Middleware;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface as Handler;
use Slim\Exception\HttpForbiddenException;
use Slim\Routing\RouteContext;
use Psr\Container\ContainerInterface;
use App\Services\PermissionService;

class PermissionMiddleware implements MiddlewareInterface {
    
    protected PermissionService $permissionService;
    protected int $timeout;

    public function __construct(ContainerInterface $container){
        $this->permissionService = $container->get('permissionService');
        $this->timeout = $container->get('settings')['security']['permission_timeout'] ?? 900; // Default to 15 mins
    }

    public function process(Request $request, Handler $handler): Response {
        if($request->getAttribute('permissions_checked')){
            return $handler->handle($request); // Skip if already checked
        }
    
        $request = $request->withAttribute('permissions_checked', true); // Cache check
    
        $userId = $_SESSION['user']['id'] ?? null;
    
        if($userId){
            if($this->permissionService->needsPermissionRefresh($userId)){
                $this->permissionService->loadPermissions($userId, $this->timeout);
            }
    
            // Get route information
            $routeContext = RouteContext::fromRequest($request);
            $route = $routeContext->getRoute();
    
            if(!$route){
                throw new HttpForbiddenException($request, 'Route not found or permission denied.');
            }
            
            $routeName = $route->getName();
            $permissions = $_SESSION['security']['permissions'][$routeName] ?? [];
    
            // Check if the user has the required permission for this route
            if(!($permissions['can_view'] ?? false)){
                throw new HttpForbiddenException($request, 'You do not have permission to view this page.');
            }
        }
    
        return $handler->handle($request);
    }
    
}
