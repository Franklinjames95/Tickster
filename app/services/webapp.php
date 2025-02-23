<?php

namespace App\Services;

use Psr\Container\ContainerInterface;

class WebApp {
    protected $container;

    public function __construct(ContainerInterface $container){
        $this->container = $container;
    }

    public function getAppVersion(): string {
        return '1.0.0.1';
    }

}
