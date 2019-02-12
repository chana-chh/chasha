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
	 * Preuzimanje singleton instance
	 *
	 * @return \App\Classes\Db self::$instance
	 */
	public static function instance()
	{
		if (!isset(self::$instance)) {
			self::$instance = new self;
		}
		return self::$instance;
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
	 * Konstruktor
	 *
	 * Postavlja instancu PDO konekcije na bazu
	 */
	private function __construct()
	{
		try {
			self::$pdo = new PDO(
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
	 * Izvrsava PDO upit
	 *
	 * @param string $sql SQL upit
	 * @param array $params Parametri za upit
	 * @return boolean $success Da li je upit uspesno izvrsen
	 */
	public static function run(string $sql, array $params = null)
	{
		try {
			$stmt = self::$pdo->prepare($sql);
			$success = $stmt->execute($params);
			self::$stmt = $stmt;
		} catch (PDOException $e) {
			self::$error = $e->getMessage();
			dd($e->getMessage(), true);
		}
		return $success;
	}

	/**
	 * Vraca podatke rezultata PDO upita
	 *
	 * @param string $sql SQL upit
	 * @param array $params Parametri za upit
	 * @param string $model Model koji se vraca
	 * @return array Niz redova u tabeli
	 */
	public static function fetch(string $sql, array $params = null, $model = null)
	{
		try {
			$stmt = self::run($sql, $params);
			if ($model) {
				$data = self::$stmt->fetchAll(PDO::FETCH_CLASS, $model);
			} else {
				$data = self::$stmt->fetchAll();
			}
		} catch (PDOException $e) {
			self::$error = $e->getMessage();
			dd($e->getMessage(), true);
		}
		return $data;
	}

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
		return self::$pdo;
	}

	/**
	 * Vraca PDOStatement instancu
	 * @return \PDOStatement
	 */
	public static function getPDOStatement()
	{
		return self::$stmt;
	}

	/**
	 * Vraca ID poslednnjeg upisa
	 *
	 * @return string
	 */
	public static function getLastInsertedId()
	{
		return self::$pdo->lastInsertId();
	}

	/**
	 * Vraca poslednji broj redova tabele
	 *
	 * @return integer
	 */
	public static function getLastRowCount()
	{
		return self::$stmt->rowCount();
	}

	/**
	 * Vraca poslednji broj kolona upita
	 *
	 * @return integer
	 */
	public static function getLastColumnCount()
	{
		return self::$stmt->columnCount();
	}

	/**
	 * Vraca poslednji izvrseni PDO upit
	 *
	 * @return string
	 */
	public static function getLastQuery()
	{
		return self::$stmt->queryString;
	}

	/**
	 * Vraca debug dump
	 *
	 * @return string
	 */
	public static function dumpDebug()
	{
		echo '<pre><code>';
		self::$stmt->debugDumpParams();
		echo '</code></pre>';
	}

	/**
	 * Vraca meta informacije o koloni
	 *
	 * @param $column_index 0-baziran indeks selektovanih kolona
	 * @return array
	 */
	public static function getColumnMeta(int $column_index)
	{
		return self::$stmt->getColumnMeta($column_index);
	}

	/**
	 * Vraca poslednju PDO gresku
	 *
	 * @return string
	 */
	public static function getLastError()
	{
		return self::$error;
	}

}
