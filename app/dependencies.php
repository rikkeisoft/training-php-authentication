<?php

use App\Middleware\BasicAuthentication;
use App\Middleware\DigestAuthentication;

// DIC configuration
$container = $app->getContainer();

// view renderer
$container['renderer'] = function ($c) {
    $settings = $c->get('settings')['renderer'];
    return new Slim\Views\PhpRenderer($settings['template_path']);
};

// monolog
$container['logger'] = function ($c) {
    $settings = $c->get('settings')['logger'];
    $logger = new Monolog\Logger($settings['name']);
    $logger->pushProcessor(new Monolog\Processor\UidProcessor());
    $logger->pushHandler(new Monolog\Handler\StreamHandler($settings['path'], $settings['level']));
    return $logger;
};

$container['auth-basic'] = function ($c) {
    $middleware = new BasicAuthentication($c->get('users')['basic']);
    $middleware->realm('Basic Authentication Demo');

    return $middleware;
};

$container['auth-digest'] = function ($c) {
    $middleware = new DigestAuthentication($c->get('users')['digest']);
    $middleware->realm('Digest Authentication Demo');
    $middleware->nonce('n0nc3');

    return $middleware;
};

$container['auth-form'] = function ($c) {
    $middleware = new \App\Middleware\HttpSessionAuthentication();
    $middleware->setLoginUrl('/login');

    return $middleware;
};

$container['csrf'] = function ($c) {
    return new \Slim\Csrf\Guard;
};