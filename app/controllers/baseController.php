<?php

namespace App\Controllers;

use Psr\Container\ContainerInterface;
use App\Config\CustomBlade as Blade;
use App\Services\DatabaseService;
use App\Services\WebApp;
use Slim\Routing\RouteParser;

abstract class BaseController {
    
    protected ContainerInterface $container;
    protected Blade $blade;
    protected DatabaseService $db;
    protected array $settings;
    protected WebApp $webapp;
    protected RouteParser $router;
    
    // /////////////////////////////////////////////////////////////////////
    abstract protected function getViewName();
    abstract protected function getPageVariables();
    // /////////////////////////////////////////////////////////////////////
    public function __construct(ContainerInterface $container){
        $this->container = $container;
        $this->blade = $container->get('blade');
        $this->settings = $container->get('settings');
        $this->db = $container->get('db');
        $this->webapp = $container->get('webapp');
        $this->router = $container->get('router');

        // Share WebApp with Blade template
        $this->blade->share('webapp', $this->webapp);
    }
    // /////////////////////////////////////////////////////////////////////
    protected function render(string $view, array $data = []){
        error_log("Rendering Blade View: $view with Data: " . json_encode($data));
        return $this->blade->run($view, $data);
    }
    // /////////////////////////////////////////////////////////////////////
    public function getPage($request, $response, array $args){
        $response->getBody()->write($this->render($this->getViewName(), $this->getPageVariables()));
        return $response;
    }
    // /////////////////////////////////////////////////////////////////////
    protected function getSettings(string $key = null, $default = null){
        return $key === null ? $this->settings : ($this->settings[$key] ?? $default);
    }
    // /////////////////////////////////////////////////////////////////////
        // Helper function to return JSON responses
    protected function jsonResponse($response, array $data, int $status = 200){
        $response->getBody()->write(json_encode($data));
        return $response->withHeader('Content-Type', 'application/json')->withStatus($status);
    }
    // /////////////////////////////////////////////////////////////////////

}
