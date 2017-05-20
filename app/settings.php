<?php
return [
    'settings' => [
        'displayErrorDetails' => true, // set to false in production
        'addContentLengthHeader' => false, // Allow the web server to send the content-length header

        // Renderer settings
        'renderer' => [
            'template_path' => __DIR__ . '/../templates/',
        ],

        // Monolog settings
        'logger' => [
            'name' => 'slim-app',
            'path' => __DIR__ . '/../logs/app.log',
            'level' => \Monolog\Logger::DEBUG,
        ],
    ],

    // TODO: change user/pass
    'users' => [
        'basic' => [
            'basic' => 'pass',
        ],
        'digest' => [
            'digest' => 'pass',
        ],
        'form' => [
            [
                'username' => 'form',
                'password' => md5('password01'),
                'state' => 1, // 1: active, 0: deleted
            ],
        ],
    ],
];
