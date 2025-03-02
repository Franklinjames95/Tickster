<?php

namespace App\Controllers;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class DashboardController extends BaseController {
    
    public function __construct(ContainerInterface $container){
        parent::__construct($container);        
    }
    
    protected function getViewName(){
        return 'dashboard';
    }

    protected function getPageVariables(){
        return [];
    }
}
