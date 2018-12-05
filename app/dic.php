<?php

$container = $app->getContainer();

// TODO: ovo je suvisno
$container['db'] = function ($container) {
    $conf = $container['settings']['db'];
    $db = new \App\Classes\Db($conf);
    return $db;
};

$container['logger'] = function ($container) {
    $conf = $container['settings']['logger'];
    $logger = new \Monolog\Logger($conf['name']);
    $file_handler = new \Monolog\Handler\StreamHandler($conf['file']);
    $logger->pushHandler($file_handler);
    return $logger;
};

$container['csrf'] = function ($container) {
    return new \Slim\Csrf\Guard;
};

$container['auth'] = function ($container) {
    return new \App\Classes\Auth(new \App\Models\Korisnik());
};

$container['flash'] = function () {
    return new \Slim\Flash\Messages();
};

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
    $view->addExtension(new Knlv\Slim\Views\TwigMessages(new Slim\Flash\Messages()));
    $view->addExtension(new Twig_Extension_Debug());
    return $view;
};
