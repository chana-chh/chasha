<?php

use App\Classes\Config;

/**
 * ChaSha
 *
 * Slim 3, Monolog, Twig
 *
 * @version v 0.0.1
 * @author ChaSha
 * @copyright Copyright (c) 2019, ChaSha
 */
require dirname(__DIR__) . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'ini.php';
Config::instance($container);
$app->run();
