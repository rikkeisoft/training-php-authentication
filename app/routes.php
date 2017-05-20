<?php

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

$app->get('/', function (Request $request, Response $response) {
    // Render index view
    return $this->renderer->render($response, 'menu.phtml');
});

$app->get('/public-area', function (Request $request, Response $response) {
    // Render index view
    return $this->renderer->render($response, 'dashboard.phtml', compact('request'));
});

$app->get('/basic-auth', function (Request $request, Response $response) {
    // Render index view
    return $this->renderer->render($response, 'dashboard.phtml', compact('request'));
})->add('auth-basic');

$app->get('/digest-auth', function (Request $request, Response $response) {
    // Render index view
    return $this->renderer->render($response, 'dashboard.phtml', compact('request'));
})->add('auth-digest');

$app->get('/login',function (Request $request, Response $response) {
    return $this->renderer->render($response, 'login.phtml');
});

$app->post('/login', \App\Action\LoginAction::class);

$app->get('/logout', function (Request $request, Response $response) {
    session_destroy();

    return $response->withStatus(301)
            ->withHeader('Location', '/');
});

$app->get('/dashboard', function (Request $request, Response $response) {

    return $this->renderer->render($response, 'dashboard.phtml', compact('request'));
})->add('auth-form');

