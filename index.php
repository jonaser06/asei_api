<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;

require  'vendor/autoload.php';


$app = new \Slim\App();

$app->post('/', function (Request $request, Response $response, $args) {
    
    $test = [
        'status'=> true,
        "message" => 'hola mundo';
    ]
    return $test;
});

$app->run();