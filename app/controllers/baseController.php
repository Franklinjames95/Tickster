<?php

namespace App\Controllers;

use Psr\Container\ContainerInterface;
use App\Config\CustomBlade as Blade;
use App\Services\WebApp;

abstract class BaseController {
    
    protected ContainerInterface $container;
    protected Blade $blade;
    protected array $settings;
    protected WebApp $webapp;

    abstract protected function getViewName();
    abstract protected function getPageVariables();

    public function __construct(ContainerInterface $container){
        $this->container = $container;
        $this->blade = $container->get('blade');
        $this->settings = $container->get('settings');
        $this->webapp = $container->get('webapp');

        // Share WebApp with Blade template
        $this->blade->share('webapp', $this->webapp);
    }

    protected function render(string $view, array $data = []){
        return $this->blade->run($view, $data);
    }

    public function getPage($request, $response, array $args){
        $response->getBody()->write($this->render($this->getViewName(), $this->getPageVariables()));
        return $response;
    }

    protected function getSettings(string $key = null, $default = null){
        return $key === null ? $this->settings : ($this->settings[$key] ?? $default);
    }
    
}