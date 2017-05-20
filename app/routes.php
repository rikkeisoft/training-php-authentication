<?php

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

/**
 * @param string $username
 * @return array|bool
 */
function getUserbyUsername($username)
{
    $users = $app->get('users')['form'];

    foreach ($users as $user) {
        if ($username === $user['username']) {
            return $user;
        }
    }

    return false;
}

/**
 * @param Response $response
 * @param $url
 * @return Response
 */
function redirectResponse(Response $response, $url)
{
    return $response->withStatus(301)
                ->withHeader('Location', $url);
}

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

$app->post('/login', function (Request $request, Response $response) {
    $input = $request->getParsedBody();

    // TODO: write login action code

//    unset($input['password']);
    return $this->renderer->render($response, 'login.phtml', compact('input'));
});

$app->get('/logout', function (Request $request, Response $response) {
    session_destroy();

    return redirectResponse($response, '/');
});

$app->get('/dashboard', function (Request $request, Response $response) {

    return $this->renderer->render($response, 'dashboard.phtml', compact('request'));
})->add('auth-form');

