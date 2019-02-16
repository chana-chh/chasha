<?php

/**
 * ChaSha - Application initialization
 *
 * Definisanje globalnih promenjivih. Ukljucivanje potrebnih fajlova.
 * Instanciranje aplikacije. Podesavanje PHP-a
 *
 * @version v 0.0.1
 * @author ChaSha
 * @copyright Copyright (c) 2019, ChaSha
 */

define('APP_NAME', 'ChaSha');

define('DS', DIRECTORY_SEPARATOR);
define('DIR', dirname(__DIR__) . DS);

require DIR . 'vendor/autoload.php';
require DIR . 'app/fje.php';
require DIR . 'app/cfg.php';

define('HOST', filter_input(INPUT_SERVER, 'REQUEST_SCHEME', FILTER_SANITIZE_STRING) . '://'
    . filter_input(INPUT_SERVER, 'SERVER_NAME', FILTER_SANITIZE_STRING));

$app = new \Slim\App($config);

require DIR . 'app/dic.php';

define('URL', HOST . $container['request']->getUri()->getBasePath() . '/');

require DIR . 'app/mid.php';
require DIR . 'app/routes.php';

date_default_timezone_set('Europe/Belgrade');

ini_set('log_errors', '1');
ini_set('log_errors_max_len', '10K');
ini_set('error_log', DIR . DS . 'app' . DS . 'tmp' . DS . 'err' . DS . 'errors.log');

ini_set('default_charset', 'UTF-8');
ini_set('magic_quotes_gpc', '0');
ini_set('register_globals', '0');
ini_set('expose_php', '0');
ini_set('allow_url_fopen', '0');
ini_set('allow_url_include', '0');

ini_set('memory_limit', '3G');
ini_set('max_execution_time', 600);

ini_set('file_uploads', 0);
ini_set('upload_max_filesize', '2M');
ini_set('upload_tmp_dir', DIR . DS . 'app' . DS . 'tmp');

if (in_array('sha512', hash_algos())) {
    ini_set('session.hash_function', 'sha512');
}
ini_set('session.hash_bits_per_character', 5);
ini_set('session.use_trans_sid', 0);
ini_set('session.use_only_cookies', 1);
ini_set('session.save_path', DIR . DS . 'app' . DS . 'tmp' . DS . 'ses');

session_start();

// Session expiration time in minutes
$sess_expire = 30;

if (isset($_SESSION['LAST_ACTION'])) {
    $sec = time() - $_SESSION['LAST_ACTION'];
    $expire = $sess_expire * 60;
    if ($sec >= $expire) {
        session_unset();
        session_destroy();
    }
}
$_SESSION['LAST_ACTION'] = time();
