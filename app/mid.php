<?php

/*
 * Middleware
 *	Redosled izvrsavanja prilikom pokretanja zahteva je
 *	4, 3, 2, 1
 *	kontroler->metoda
 *	1, 2, 3, 4
 */

$app->add(new \App\Middlewares\ValidationErrorsMiddleware($container)); // 1
$app->add(new \App\Middlewares\OldMiddleware($container)); // 2
$app->add(new \App\Middlewares\CsrfMiddleware($container)); // 3
$app->add($container->csrf); // 4
