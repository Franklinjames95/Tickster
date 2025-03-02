<?php

date_default_timezone_set('Europe/London');

use Slim\Factory\AppFactory;
use DI\Container;
use App\Middleware\ErrorHandlerMiddleware;

require __DIR__ . '/../vendor/autoload.php';

if(false){ // Should be set to true in production
	$containerBuilder->enableCompilation(__DIR__ . '/../cache');
}

// Start the session
session_start();

// Create Container
$container = new Container();

// Set up Slim with DI container
AppFactory::setContainer($container);
$app = AppFactory::create();

// Load dependencies
$settings = require __DIR__ . '/../app/config/settings.php';
(require $settings['paths']['dependencies'])($app, $container);

$app->addBodyParsingMiddleware();

// Load routes dynamically from settings
(require $settings['paths']['routes'])($app);

// Register Routing Middleware (IMPORTANT)
$app->addRoutingMiddleware();

// Add Error Middleware with Custom Handler
$app->addErrorMiddleware(true, true, true)->setDefaultErrorHandler(new ErrorHandlerMiddleware($app));

// Run the app
$app->run();
