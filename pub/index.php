<?php

use App\Classes\Db;
use App\Classes\Model;
use App\Classes\Config;
use App\Models\Korisnik;

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

$model = new Korisnik();
$sql =  "SELECT predmeti.*,
        korisnici.name AS korisnik,
        s_vrste_upisnika.naziv AS vrsta,
        s_vrste_upisnika.slovo AS vrsta_slovo,
        CONCAT(s_vrste_upisnika.slovo, '-', predmeti.broj_predmeta, '/', predmeti.godina_predmeta) AS broj,
        tuzioci.komintent_id
        FROM predmeti
        LEFT JOIN korisnici ON predmeti.korisnik_id = korisnici.id
        LEFT JOIN s_vrste_upisnika ON predmeti.vrsta_upisnika_id = s_vrste_upisnika.id
        LEFT JOIN tuzioci ON predmeti.id = tuzioci.predmet_id
        GROUP BY predmeti.id
        LIMIT 100;";
$result = $model->fetch($sql);

dd($result, true);
$app->run();
