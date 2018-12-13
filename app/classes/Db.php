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
 * Db za PDO MySQL
 *
 * @author ChaSha
 */
class Db
{

	/**
	 * PDO instanca
	 * @var \PDO
	 */
	private $pdo;

	/**
	 * PDO greska
	 * @var string
	 */
	private $error;

	/**
	 * Broj redova u tabeli na koje je upit uticao
	 * @var integer
	 */
	private $count;

	/**
	 * Poslednji upit koji je izvrsio PDO
	 * @var string
	 */
	private $lastQuery;

	/**
	 * Konstruktor
	 *
	 * Postavlja instancu PDO konekcije na bazu
	 */
	public function __construct($config)
	{
		try {
			$this->pdo = new PDO($config['dsn'], $config['username'], $config['password'], $config['options']);
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
	public function qry(string $sql, array $params = null)
	{
		try {
			$stmt = $this->pdo->prepare($sql);
			if ($params) {
				foreach ($params as $key => $value) {
					$stmt->bindValue($key, $value, $this->pdoType($value));
				}
			}
			$stmt->execute();
			$this->count = (int)$stmt->rowCount();
			$this->lastQuery = $stmt->queryString;
		} catch (PDOException $e) {
			$this->error = $e->getMessage();
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
	public function sel($sql, $params = null, $model = null, $args = null)
	{
		try {
			$stmt = $this->qry($sql, $params);
			if ($model) {
				$data = $stmt->fetchAll(PDO::FETCH_CLASS, $model, $args);
			} else {
				$data = $stmt->fetchAll();
			}
		} catch (PDOException $e) {
			$this->error = $e->getMessage();
		}
		return $this->count === 1 ? $data[0] : $data;
	}

	/**
	 * Odredjuje PDO tip parametra
	 *
	 * @param mixed $param Parametar za upit
	 * @return integer PDO tip parametra
	 */
	protected function pdoType($param)
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
	public function getPDO()
	{
		return $this->pdo;
	}

	/**
	 * Vraca posledni uneti ID
	 *
	 * @return string
	 */
	public function getLastId()
	{
		return $this->pdo->lastInsertId();
	}

	/**
	 * Vraca poslednji broj redova tabele
	 *
	 * @return integer
	 */
	public function getLastCount()
	{
		return $this->count;
	}

	/**
	 * Vraca poslednju PDO gresku
	 *
	 * @return string
	 */
	public function getLastError()
	{
		return $this->error;
	}

	/**
	 * Vraca poslednji izvrseni PDO upit
	 *
	 * @return string
	 */
	public function lastQuery()
	{
		return $this->lastQuery;
	}

}
