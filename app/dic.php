<?php

/**
 * ChaSha - Slim dependency container
 *
 * Postavljanje instanci klasa u DC radi kasnijeg koriscenja
 *
 * @version v 0.0.1
 * @author ChaSha
 * @copyright Copyright (c) 2019, ChaSha
 */

/**
 * App dependency container
 * @var array $container
 */
$container = $app->getContainer();

// Monolog instance - logger
$container['logger'] = function ($container) {
    $conf = $container['settings']['logger'];
    $logger = new \Monolog\Logger($conf['name']);
    $file_handler = new \Monolog\Handler\StreamHandler($conf['file']);
    $logger->pushHandler($file_handler);
    return $logger;
};

// CSRF protection instance - csrf
$container['csrf'] = function ($container) {
    return new \Slim\Csrf\Guard;
};

// Validation instance - validator
$container['validator'] = function ($container) {
    return new \App\Classes\Validator();
};

// Authorization instance - auth
$container['auth'] = function ($container) {
    return new \App\Classes\Auth(new \App\Models\Korisnik());
};

// Flash messages instance - flash
$container['flash'] = function () {
    return new \Slim\Flash\Messages;
};

// Twig view instance - view
$container['view'] = function ($container) {
    $conf = $container['settings']['renderer'];
    $view = new \Slim\Views\Twig($conf['template_path'], ['cache' => $conf['cache_path'], 'debug' => true]);
    $router = $container->router;
    $uri = $container->request->getUri();
    $view->addExtension(new \Slim\Views\TwigExtension($router, $uri));
    $view->getEnvironment()->addGlobal('auth', [
        'logged' => $container->auth->isLoggedIn(),
        'user' => $container->auth->user(),
    ]);
    $view->addExtension(new Knlv\Slim\Views\TwigMessages(new Slim\Flash\Messages));
    $view->addExtension(new Twig_Extension_Debug);
    return $view;
};
