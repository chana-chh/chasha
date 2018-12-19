<?php

/**
 * Database PDO wrapper
 *
 * Osnovna klasa za maipulaciju bazom podataka
 * Za pokusaj duplog unosa koristiti nesto kao
 * if($e->errorInfo[1] === 1062) echo 'Duplicate entry';
 *
 * @version v 0.0.1
 * @author ChaSha
 * @copyright Copyright (c) 2019, ChaSha
 */

namespace App\Classes;

use \PDO;

/**
 * PDO MySQL wrapper
 *
 * @author ChaSha
 */
class Db
{

	/**
	 * Singleton instanca Db
	 * @var \App\Classes\Db
	 */
	private static $instance = null;

	/**
	 * PDO instanca
	 * @var \PDO
	 */
	private static $pdo;
	
	/**
	 * PDO instanca
	 * @var \PDOStatement
	 */
	private static $stmt;

	/**
	 * PDO greska
	 * @var string
	 */
	private static $error;

	/**
	 * Broj redova u tabeli na koje je upit uticao
	 * @var integer
	 */
	private static $count;

	/**
	 * Poslednji upit koji je izvrsio PDO
	 * @var string
	 */
	private static $lastQuery;

	/**
	 * Preuzimanje singleton instance
	 * 
	 * @return \App\Classes\Db static::$instance
	 */
	public static function instance()
	{
		if (!isset(static::$instance)) {
            static::$instance = new static;
        }
        return static::$instance;
	}
	
	private function __clone() {}
	private function __sleep() {}
	private function __wakeup() {}

	/**
	 * Konstruktor
	 *
	 * Postavlja instancu PDO konekcije na bazu
	 */
	private function __construct()
	{
		try {
			static::$pdo = new PDO(
				Config::get('db.dsn'),
				Config::get('db.username'),
				Config::get('db.password'),
				Config::get('db.options')
			);
		} catch (PDOException $e) {
			self::$error = $e->getMessage();
		}
	}

	/**
	 * Izvrsava PDO upit koji ne vraca rezultat
	 *
	 * INSERT, UPDATE, DELETE
	 *
	 * @param string $sql SQL upit
	 * @param array $params Parametri za upit
	 * @return \PDOStatement
	 */
	public static function run(string $sql, array $params = null)
	{
		try {
			$stmt = static::$pdo->prepare($sql);
			$stmt->execute($params);
			static::$count = (int)$stmt->rowCount();
			static::$lastQuery = $stmt->queryString;
			static::$stmt = $stmt;
		} catch (PDOException $e) {
			static::$error = $e->getMessage();
		}
		return $stmt;
	}

	/**
	 * Izvrsava PDO upit koji vraca rezultat
	 *
	 * SELECT
	 *
	 * @param string $sql SQL upit
	 * @param array $params Parametri za upit
	 * @param string $model Model koji se vraca
	 * @return array Niz Model-a koji predstavljaju red u tabeli
	 */
	public static function fetch(string $sql, array $params = null)
	{
		try {
			static::run($sql, $params);
			$data = static::$stmt->fetchAll();
			static::$count = (int)static::$stmt->rowCount();
		} catch (PDOException $e) {
			static::$error = $e->getMessage();
		}
		return $data;
	}

	public static function raw(string $sql, array $params = null){}
	public static function select(string $sql, array $params = null){}
	public static function insert(string $sql, array $params = null){}
	public static function update(string $sql, array $params = null){}
	public static function delete(string $sql, array $params = null){}

	/**
	 * Odredjuje PDO tip parametra
	 *
	 * @param mixed $param Parametar za upit
	 * @return integer PDO tip parametra
	 */
	protected static function pdoType($param)
	{
		switch (gettype($param)) {
			case 'NULL':
				return PDO::PARAM_NULL;
			case 'boolean':
				return PDO::PARAM_BOOL;
			case 'integer':
				return PDO::PARAM_INT;
			default:
				return PDO::PARAM_STR;
		}
	}

	/**
	 * Vraca PDO instancu
	 * @return \PDO
	 */
	public static function getPDO()
	{
		return static::$pdo;
	}

	/**
	 * Vraca PDOStatement instancu
	 * @return \PDOStatement
	 */
	public static function getPDOStatement()
	{
		return static::$stmt;
	}

	/**
	 * Vraca posledni uneti ID
	 *
	 * @return string
	 */
	public static function getLastInsertedId()
	{
		return static::$pdo->lastInsertId();
	}

	/**
	 * Vraca poslednji broj redova tabele
	 *
	 * @return integer
	 */
	public static function getLastCount()
	{
		return static::$count;
	}

	/**
	 * Vraca poslednju PDO gresku
	 *
	 * @return string
	 */
	public static function getLastError()
	{
		return static::$error;
	}

	/**
	 * Vraca poslednji izvrseni PDO upit
	 *
	 * @return string
	 */
	public static function lastQuery()
	{
		return static::$lastQuery;
	}

	public static function quote(string $string)
	{
		return static::$pdo->quote($string);
	}
	
	public static function getColumnMeta(int $column_number)
	{
		return static::$stmt->getColumnMeta($column_number);
	}
}
