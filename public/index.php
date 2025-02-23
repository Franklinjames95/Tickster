<?php

use Slim\Factory\AppFactory;
use DI\Container;
use App\Middleware\ErrorHandlerMiddleware;

require __DIR__ . '/../vendor/autoload.php';

// Create Container
$container = new Container();

// Load dependencies
$settings = require __DIR__ . '/../app/config/settings.php';
(require $settings['paths']['dependencies'])($container);

// Set up Slim with DI container
AppFactory::setContainer($container);
$app = AppFactory::create();

// Load routes dynamically from settings
(require $settings['paths']['routes'])($app);

// Add Error Middleware with Custom Handler
$app->addErrorMiddleware(true, true, true)->setDefaultErrorHandler(new ErrorHandlerMiddleware($app));

// Run the app
$app->run();
