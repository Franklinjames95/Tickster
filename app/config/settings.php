<?php

use Lib\EnvReader;
EnvReader::load(__DIR__ . '/../../.env');

return [
    'app' => [
        'name' => 'Frank/Tickster',
        'debug' => true,
    ],
    'paths' => [
        'root' => __DIR__ . '/../..',                       
        'app' => __DIR__ . '/..',                           
        'public' => __DIR__ . '/../../public',              
        'views' => __DIR__ . '/../views',                   
        'cache' => __DIR__ . '/../../cache',                
        'routes' => __DIR__ . '/../routes.php',             
        'dependencies' => __DIR__ . '/dependencies.php',    
        'logs' => __DIR__ . '/../../logs'                   
    ],
    'database' => [
        'host' => EnvReader::get('DB_HOST'),
        'name' => EnvReader::get('DB_NAME'),
        'user' => EnvReader::get('DB_USER'),
        'pass' => EnvReader::get('DB_PASS'),
    ],
    'jwt' => [
        'secret' => 'my-secret-key',
        'exp' => 60 * 60 * 24, // 1 day expiration
        'issuer' => 'some-domain.com'
    ],
    'security' => [
        'permission_timeout' => 900  // Timeout in seconds (15 minutes)
    ]
];
