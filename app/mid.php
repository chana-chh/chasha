<?php

/**
 * ChaSha - Registracija middleware-a
 *
 * Redosled izvrsavanja middleware-a prilikom pokretanja zahteva je 4, 3, 2, 1
 * zatim se izvrsi metoda kontrolera, pa opet middleware 1, 2, 3, 4
 *
 * @version v 0.0.1
 * @author ChaSha
 * @copyright Copyright (c) 2019, ChaSha
 */

$app->add(new \App\Middlewares\ValidationErrorsMiddleware($container)); // 1
$app->add(new \App\Middlewares\OldMiddleware($container)); // 2
$app->add(new \App\Middlewares\CsrfMiddleware($container)); // 3
$app->add($container->csrf); // 4
