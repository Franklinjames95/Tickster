<?php

namespace App\Services;

use Psr\Container\ContainerInterface;
use Slim\Routing\RouteParser;

class WebApp {
    protected $container;
    protected RouteParser $router;

    public function __construct(ContainerInterface $container){
        $this->container = $container;
        $this->router = $container->get('router');
    }

    public function getAppVersion(): string {
        return '1.0.0.1';
    }

    public function route(string $name, array $params = []): string {
        return $this->router->urlFor($name, $params);
    }

}
