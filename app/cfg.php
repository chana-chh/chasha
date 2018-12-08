<?php

/**
 * ChaSha - konfiguracija aplikacije
 *
 * @version v 0.0.1
 * @author ChaSha
 * @copyright Copyright (c) 2019, ChaSha
 */

/**
 * Konfiguracija Slim-a
 * @var array $config
 */
$config = [
    'settings' => [
        'displayErrorDetails' => true,
        'logger' => [
            'name' => 'monologger',
            'file' => DIR . 'app' . DS . 'tmp' . DS . 'log' . DS . 'app.log',
        ],
        'renderer' => [
            'template_path' => DIR . 'app' . DS . 'views',
            'cache_path' => false, // DIR . 'app' . DS . 'tmp' . DS . 'cache',
        ],
    ],
];
