<?php

/**
 * ChaSha - Putanje (rute) aplikacije
 *
 * Sve putanje aplikacije
 *
 * @version v 0.0.1
 * @author ChaSha
 * @copyright Copyright (c) 2019, ChaSha
 */

use App\Middlewares\AuthMiddleware;
use App\Middlewares\GuestMiddleware;

$app->get('/', '\App\Controllers\HomeController:getHome')->setName('pocetna');

$app->group('', function () {
	$this->get('/registracija', '\App\Controllers\AuthController:getRegistracija')->setName('registracija');
	$this->post('/registracija', '\App\Controllers\AuthController:postRegistracija');
	$this->get('/prijava', '\App\Controllers\AuthController:getPrijava')->setName('prijava');
	$this->post('/prijava', '\App\Controllers\AuthController:postPrijava');
})->add(new GuestMiddleware($container));

$app->group('', function () {
	$this->get('/odjava', '\App\Controllers\AuthController:getOdjava')->setName('odjava');
	$this->get('/strana', '\App\Controllers\HomeController:getPagination')->setName('strainicenje');
})->add(new AuthMiddleware($container));
