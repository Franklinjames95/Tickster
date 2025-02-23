<?php

use DI\Container;
use App\Services\WebApp;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Psr\Log\LoggerInterface;
use App\Config\customBlade as Blade;
use App\Services\PermissionService;

return function(Container $container){
    
    // Load settings once and store in the container
    $container->set('settings', fn() => include __DIR__ . '/settings.php');

    // Register BladeOne using settings from the container
    $container->set('blade', fn() => new Blade(
        $container->get('settings')['paths']['views'],
        $container->get('settings')['paths']['cache']
    ));

    // Register WebApp
    $container->set('webapp', fn() => new WebApp($container));

    // Register Logger
    $container->set(LoggerInterface::class, fn() => (new Logger('app'))
        ->pushHandler(new StreamHandler($container->get('settings')['paths']['logs'] . '/app.log'))
    );

    // Register Database Connection (SQL Server using PDO)
    $container->set('db', function(Container $c){
        $settings = $c->get('settings')['database'];
        $dsn = "sqlsrv:Server={$settings['host']};Database={$settings['name']}";
        return new PDO($dsn, null, null, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
    });

    // Register PermissionService
    $container->set('permissionService', fn() => new PermissionService($container));

};
