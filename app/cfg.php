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
        'db' => [
            'dsn' => 'mysql:host=127.0.0.1;dbname=jp;charset=utf8mb4',
            'username' => 'root',
            'password' => '',
            'options' => [
                PDO::ATTR_PERSISTENT => true,
                PDO::ATTR_EMULATE_PREPARES => false, // [true] za php verzije manje od 5.1.17 ?
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,
			    // PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8mb4', // za php verzije manje od 5.3.6 ?
                PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true,
            ],
        ],
    ],
];
