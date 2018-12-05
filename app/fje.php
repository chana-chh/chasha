<?php

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

function error($opis, $parametar = null)
{

    echo '<h1 style="font-family: sans-serif; color: red">GREŠKA</h1>';
    echo '<p style="font-family: sans-serif; font-size: 18px;">' . $opis . '</p>';
    if ($parametar) {
        echo '<p style="font-family: monospace; font-size: 18px;"><code>[' . $parametar . ']</code></p>';
    }
    die();
}

function dd($v, $print = false, $die = true)
{

    echo '<h3 style="color:#900">VARIABLE</h1>';
    echo '<pre style="background-color:#fdd; color:#000; padding:1rem;">';
    if ($print) {
        print_r($v);
    } else {
        var_dump($v);
    }
    echo '</pre>';

    if (gettype($v) === 'object') {
        echo '<h3 style="color:#090">Object: ' . get_class($v) . '</h1>';
        echo '<pre style="background-color:#dfd; color:#000; padding:1rem;">';
        print_r(get_class_methods($v));
        echo '</pre>';
    }

	// echo '<h3 style="color:#009">BACKTRACE</h1>';
    // echo '<pre style="background-color:#ddf; color:#000; padding:1rem;">';
    // print_r(debug_backtrace());
    // echo '</pre>';

    if ($die) {
        die();
    }
}
