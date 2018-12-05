<?php

$app->get('/', '\App\Controllers\HomeController:getHome')->setName('pocetna');

$app->get('/registracija', '\App\Controllers\AuthController:getRegistracija')->setName('registracija');
$app->post('/registracija', '\App\Controllers\AuthController:postRegistracija');

$app->get('/prijava', '\App\Controllers\AuthController:getPrijava')->setName('prijava');
$app->post('/prijava', '\App\Controllers\AuthController:postPrijava');

$app->get('/odjava', '\App\Controllers\AuthController:getOdjava')->setName('odjava');
