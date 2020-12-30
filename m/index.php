<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;

require  'vendor/autoload.php';

$config = [
    'displayErrorDetails' => true, 
];
$app = new \Slim\App($config);


require_once 'routes/routes.php';
$app->run();