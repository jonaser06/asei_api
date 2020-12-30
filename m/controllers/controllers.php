<?php
require_once 'models/models.php';
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;


$app->get('/login', function (Request $request, Response $response, $args) {
    
    $data = [
        'data' => 'res'
    ];
    $response->getBody()->write(json_encode($data));
    return $response;
});