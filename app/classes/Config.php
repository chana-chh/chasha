<?php

/**
 * Klasa za cuvanje podesavanja i
 * Slim\Container-a (zbog lakseg pristupa)
 *
 * @version v 0.0.1
 * @author ChaSha
 * @copyright Copyright (c) 2019, ChaSha
 */

namespace App\Classes;

use \Exception;

/**
 * Klasa koja cuva konfiguraciju aplikacije
 *
 * @author ChaSha
 */
final class Config
{

    /**
     * Singleton instanca Config
     * @var \App\Classes\Config
     */
    private static $instance = null;

    /**
     * Kontejner aplikacije
     * @var \Slim\Container
     */
    private static $container;

    /**
     * Kompletna podesavanja aplikacije
     * @var array
     */
    private static $config = [
        'cyrillic' => false, // da li je aplikacija cirilicna
        'pagination' => [
            // Podesavanja za stranicenje
            'per_page' => 10,
            'page_span' => 3,
        ],
        'db' => [
            // Podesavanja za PDO MySQL konekciju
            'dsn' => 'mysql:host=127.0.0.1;dbname=kartoteka;charset=utf8mb4',
            'username' => 'root',
            'password' => '',
            'options' => [
                \PDO::ATTR_PERSISTENT => true,
                \PDO::ATTR_EMULATE_PREPARES => false, // [true] za php verzije manje od 5.1.17 ?
                \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_OBJ,
			    // PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8mb4', // za php verzije manje od 5.3.6 ?
                \PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true,
            ],
        ],
    ];

    /**
     * Preuzimanje singleton instance
     *
     * @return \App\Classes\Config static::$instance
     */
    public static function instance($container)
    {
        if (!isset(static::$instance)) {
            static::$instance = new static($container);
        }
        return static::$instance;
    }

    /**
     * Konstruktor
     *
     * Postavlja kontejner
     */
    private function __construct($container)
    {
        static::$container = $container;
    }
    private function __clone()
    {
    }
    private function __sleep()
    {
    }
    private function __wakeup()
    {
    }

    /**
     * Vraca ceo kontejner ili instancu iz kontejnera
     *
     * @param string $instance_name Naziv instance koja se preuzima
     * @return mixed
     */
    public static function getContainer($instance_name = null)
    {
        if ($instance_name === null) {
            return static::$container;
        }
        if (isset(static::$container[$instance_name])) {
            return static::$container[$instance_name];
        }
        throw new Exception("U kontejneru ne postoji instanca {$instance_name}");
    }

    /**
     * Vraca kompletnu konfiguraciju ili odredjeno podesavanje
     * iz konfiguracije
     *
     * Naziv podesavanja je u obliku 'grupa.podgrupa.podesavanje'
     *
     * @param string $key Naziv podesavanja koje se trazi
     * @param mixed $default Podrazumevana vrednost ako nije pronadjeno podesavanje
     * @return mixed
     */
    public static function get(string $key = null, $default = null)
    {
        if ($key === null) {
            return static::$config;
        }
        if (!is_string($key) || empty($key)) {
            throw new Exception("Naziv konfiguracije nije ispravan");
        }
        $data = static::$config;
        if (strpos($key, '.') !== false) {
            $keys = explode('.', $key);
            foreach ($keys as $k) {
                if (!isset($data[$k])) {
                    return $default;
                }
                if (!is_array($data)) {
                    return $default;
                }
                $data = $data[$k];
            }
        } else {
            return isset($data[$key]) ? $data[$key] : $default;
        }
        return $data === null ? $default : $data;
    }

}
