<?php

namespace App\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class HomeController extends BaseController {

    protected function getViewName(){
        return 'home';
    }

    protected function getPageVariables(){
        return [];
    }
}
