<?php

use Slim\App;
use DI\Container;
use App\Services\WebApp;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Psr\Log\LoggerInterface;
use App\Config\CustomBlade as Blade;
use App\Services\PermissionService;
use App\Services\DatabaseService;
use App\Middleware\PublicRouteMiddleware;
use App\Middleware\PermissionMiddleware;

return function(App $app, Container $container){

    // Register Route Parser first!
    $container->set('router', function() use ($app){
        return $app->getRouteCollector()->getRouteParser();
    });

    // Load settings once and store in the container
    $container->set('settings', fn() => include __DIR__ . '/settings.php');

    // Register BladeOne using settings from the container
    $container->set('blade', fn() => new Blade(
        $container->get('settings')['paths']['views'],
        $container->get('settings')['paths']['cache']
    ));    

    // Register WebApp
    $container->set('webapp', fn() => new WebApp($container));

    // Register Public router
    $container->set('PublicRouteMiddleware', fn() => new PublicRouteMiddleware($container));
    $container->set('PermissionMiddleware', fn() => new PermissionMiddleware($container));

    // Register Loggers
    $container->set('logger', function (Container $c) {
        $logsPath = $c->get('settings')['paths']['logs'];
    
        return [
            'app' => (new Logger('app'))->pushHandler(new StreamHandler("$logsPath/app.log", Logger::DEBUG)),
            'sql' => (new Logger('sql'))->pushHandler(new StreamHandler("$logsPath/sql.log", Logger::DEBUG))
        ];
    });

    // Create and store loggers
    $container->set(LoggerInterface::class, fn ($c) => $c->get('logger')['app']);
    $container->set('sqlLogger', fn ($c) => $c->get('logger')['sql']);

    // Register database service
    $container->set('db', function(Container $c){
        return new DatabaseService(
            $c->get('settings')['database'],
            $c->get(LoggerInterface::class),  // Main app logger
            $c->get('sqlLogger')  // SQL-specific logger
        );
    });

    // Register PermissionService
    $container->set('permissionService', fn() => new PermissionService($container));

};
