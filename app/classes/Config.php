<?php

namespace App\Classes;

use \Exception;

final class Config
{

    private static $instance = null;
    private static $container;
    private static $config = [
        'pagination' => [
            'per_page' => 10,
            'page_span' => 3,
            'css_class' => 'pgn-btn',
            'css_current_class' => 'pgn-cur-btn',
        ],
        'db' => [
            'dsn' => 'mysql:host=127.0.0.1;dbname=jp;charset=utf8mb4',
            'username' => 'root',
            'password' => '',
            'options' => [
                \PDO::ATTR_PERSISTENT => true,
                \PDO::ATTR_EMULATE_PREPARES => false, // [true] za php verzije manje od 5.1.17 ?
                \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
			    // PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8mb4', // za php verzije manje od 5.3.6 ?
                \PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true,
            ],
        ],
    ];

    public static function instance($container)
    {
        if (!isset(static::$instance)) {
            static::$instance = new static($container);
        }
        return static::$instance;
    }

    private function __construct($container)
	{
		static::$container = $container;
	}
	private function __clone() {}
	private function __sleep() {}
    private function __wakeup() {}
        
    public static function getCountainer($object_name = null)
    {
        if($object_name === null) {
            return static::$container;
        }
        if(isset(static::$container[$object_name])) {
            return static::$container[$object_name];
        }
        throw new Exception("U kontejneru ne postoji {$object_name}",1);
    }

    public static function get($key = null, $default = null)
    {
        if($key === null) {
            return static::$config;
        }
        if (!is_string($key) || empty($key)) {
            throw new Exception("Naziv konfiguracije nije ispravan",1);
        }
        $data = static::$config;
        if (strpos($key, '.') !== false) {
            $keys = explode('.', $key);
            foreach ($keys as $k) {
                if (!isset($data[$k]) ) {
                    return $default;
                }
                if (!is_array($data)) {
                    return $default;
                }
                $data = $data[$k];
            }
        }
        return $data === null ? $default:$data;
    }

}
