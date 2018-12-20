<?php

use App\Classes\Db;
use App\Classes\Model;
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
session_start();
Config::instance($container);

$model = new Model();

$result = $model->insert();

dd($result);

$app->run();
