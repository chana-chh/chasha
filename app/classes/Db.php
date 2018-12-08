<?php

namespace App\Classes;

use PDO;

class Db
{

	private $pdo = null;
	private $error;
	private $count;
	private $lastQuery;
	private $config = [
		'dsn' => 'mysql:host=127.0.0.1;dbname=jp;charset=utf8',
		'username' => 'root',
		'password' => '',
		'options' => [
			PDO::ATTR_PERSISTENT => true,
			PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
			PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,
			PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8mb4',
			PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true,
		],
	];

	public function __construct()
	{
		try {
			$this->pdo = new PDO($this->config['dsn'], $this->config['username'], $this->config['password'], $this->config['options']);
		} catch (PDOException $e) {
			self::$error = $e->getMessage();
			greska('Bre, nemos se okachi, bre.', $e->getMessage());
		}

	}

	public function connection()
	{
		return $this->pdo;
	}

	public function qry($sql, $params = null)
	{
		try {
			$stmt = $this->pdo->prepare($sql);
			if ($params) {
				foreach ($params as $key => $value) {
					$stmt->bindValue($key, $value, $this->pdoType($value));
				}
			}
			$stmt->execute();
			$this->count = $stmt->rowCount();
			$this->lastQuery = $stmt->queryString;
		} catch (PDOException $e) {
			$this->error = $e->getMessage();
			greska('Bre, nece pukne upit, bre.', $e->getMessage());
		}

		return $stmt;
	}

	public function sel($sql, $params = null, $model = null)
	{
		try {
			$stmt = $this->qry($sql, $params);
			if ($model) {
				$data = $stmt->fetchAll(PDO::FETCH_CLASS, $model);
			} else {
				$data = $stmt->fetchAll();
			}
		} catch (PDOException $e) {
			$this->error = $e->getMessage();
			greska('Bre, nece povuche podatke, bre.', $e->getMessage());
		}
		return $this->count === 1 ? $data[0] : $data;
	}

	public function foundRows()
	{
		$count = $this->sel("SELECT FOUND_ROWS() AS count;");
		return (int)$count[0]->count;
	}

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

	public function lastId()
	{
		return $this->pdo->lastInsertId();
	}

	public function lastCount()
	{
		return $this->count;
	}

	public function lastError()
	{
		return $this->error;
	}

	public function lastQuery()
	{
		return $this->lastQuery;
	}

}
