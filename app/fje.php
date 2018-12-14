<?php

/**
 * ChaSha - Pomocne funkcije (helper funkcije)
 *
 * @version v 0.0.1
 * @author ChaSha
 * @copyright Copyright (c) 2019, ChaSha
 */

/**
 * Vraca deo stringa izmedju dva stringa
 *
 * @param string $string String koji se pretrazuje
 * @param string $start Prvi string
 * @param string $end Drugi string
 * @return string Deo izmedju prvog i drugog stringa
 */
function getStringBetween($string, $start, $end)
{
    $string = " " . $string;
    $ini = strpos($string, $start);
    if ($ini == 0)
        return "";
    $ini += strlen($start);
    $len = strpos($string, $end, $ini) - $ini;
    return substr($string, $ini, $len);
}

/**
 * Konverter latinica-cirilica-latinica
 *
 * @param string $tekst Tekst koji se konvertuje
 * @param boolean $cirilica_u_latinicu Da li je konverzija cir u lat (default je lat u cir)
 */
function latinicaUCirilicu(string $tekst, bool $cirilica_u_latinicu = false)
{
    $latinica = [
        'Đ', 'Dj', 'DJ', 'Lj', 'LJ', 'Nj', 'NJ', 'Dž', 'DŽ',
        'A', 'B', 'V', 'G', 'D', 'E', 'Ž', 'Z', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'R', 'S', 'T', 'Ć', 'U', 'F', 'H', 'C', 'Č', 'Š',
        'đ', 'dj', 'lj', 'nj', 'dž',
        'a', 'b', 'v', 'g', 'd', 'e', 'ž', 'z', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'r', 's', 't', 'ć', 'u', 'f', 'h', 'c', 'č', 'š',
    ];
    $cirilica = [
        'Ђ', 'Ђ', 'Ђ', 'Љ', 'Љ', 'Њ', 'Њ', 'Џ', 'Џ',
        'А', 'Б', 'В', 'Г', 'Д', 'Е', 'Ж', 'З', 'И', 'Ј', 'К', 'Л', 'М', 'Н', 'О', 'П', 'Р', 'С', 'Т', 'Ћ', 'У', 'Ф', 'Х', 'Ц', 'Ч', 'Ш',
        'ђ', 'ђ', 'љ', 'њ', 'џ',
        'а', 'б', 'в', 'г', 'д', 'е', 'ж', 'з', 'и', 'ј', 'к', 'л', 'м', 'н', 'о', 'п', 'р', 'с', 'т', 'ћ', 'у', 'ф', 'х', 'ц', 'ч', 'ш',
    ];
    if ($cirilica_u_latinicu) {
        return str_replace($cirilica, $latinica, $tekst);
    } else {
        return str_replace($latinica, $cirilica, $tekst);
    }
}

/**
 * Prikaz greske
 *
 * @param string $description Opis greske
 * @param mixed $param Parametar koji detaljnije opisuje gresku
 */
function error($description, $param = null)
{
    echo '<h1 style="font-family: sans-serif; color: red">GREŠKA</h1>';
    echo '<p style="font-family: sans-serif; font-size: 18px;">' . $description . '</p>';
    if ($param) {
        echo '<p style="font-family: monospace; font-size: 18px;"><code>[' . $param . ']</code></p>';
    }
    die();
}

/**
 * Dump promenjive
 *
 * @param mixed $var Promenjiva koja se dump-uje
 * @param boolean $print Da li se koristi print_r umesto podrazumevanog var_dump
 * @param boolean $die Da li se prekida dalje izvrsavanje skripta
 * @param boolean $backtrace Da li se prikazuje i backtrace (debug_backtrace)
 */
function dd($var, $print = false, $die = true, $backtrace = false)
{
    echo '<h3 style="color:#900">VARIABLE</h1>';
    echo '<pre style="background-color:#fdd; color:#000; padding:1rem;">';
    if ($print) {
        print_r($var);
    } else {
        var_dump($var);
    }
    echo '</pre>';

    if (gettype($var) === 'object') {
        echo '<h3 style="color:#090">OBJECT: ' . get_class($var) . '</h1>';
        echo '<pre style="background-color:#dfd; color:#000; padding:1rem;">';
        print_r(get_class_methods($var));
        echo '</pre>';
    }

    if ($backtrace) {
        echo '<h3 style="color:#009">BACKTRACE</h1>';
        echo '<pre style="background-color:#ddf; color:#000; padding:1rem;">';
        print_r(debug_backtrace());
        echo '</pre>';
    }

    if ($die) {
        die();
    }
}
