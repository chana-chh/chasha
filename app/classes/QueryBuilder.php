<?php

/**
 * QueryBuilder za MySQL
 * 
 * Detaljan opis
 * 
 * @version v 0.0.1
 * @author ChaSha
 * @copyright Copyright (c) 2019, ChaSha
 */

namespace App\Classes;

/**
 * QueryBuilder za MySQL
 * 
 * @author ChaSha
 */
class QueryBuilder
{

	/**
	 * Konstante za tip upita
	 */
	protected const SELECT = 1; // select
	protected const INSERT = 2; // insert
	protected const UPDATE = 3; // update, where, oredrBy, limit
	protected const DELETE = 4; // delete, where, oredrBy, limit

	/**
	 * SELECT, INSERT, UPDATE, DELETE
	 * @var integer
	 */
	protected $type;

	/**
	 * Naziv tabele u db
	 * @var string
	 */
	protected $table;

	/**
	 * Filtriranje za SELECT, UPDATE, DELETE
	 * @var array
	 */
	protected $wheres;

	/**
	 * Kolone za SELECT, INSERT, UPDATE
	 * @var array
	 */
	protected $columns;

	/**
	 * Bind parametri
	 * @var array
	 */
	protected $parameters;

	/**
	 * Sortiranje za SELECT, UPDATE, DELETE
	 * @var array
	 */
	private $orders;

	/**
	 * Limit za SELECT, UPDATE, DELETE
	 * @var array
	 */
	private $limit;

	/**
	 * SQl izraz - krajnji rezultat QueryBuilder-a
	 * @var string
	 */
	protected $sql = '';

	/**
	 * Konstruktor
	 * 
	 * $qb = new QueryBuilder('tabela')
	 * @param string $table Naziv tabele
	 */
	public function __construct(string $table)
	{
		$this->table = $table;
	}

	/**
	 * ORDER BY - sortiranje podataka
	 * 
	 * $qb->orderBy('godina DESC', 'broj ASC');
	 * @param mixed $orders Niz sortiranja ili svako sortiranje kao poseban argument
	 * @return \App\Classes\QueryBuilder $this
	 */
	public function orderBy($orders)
	{
		$ord = array_map('trim', func_get_args());
		$this->orders = array_merge((array)$this->orders, $ord);
		return $this;
	}

	/**
	 * LIMIT - ogranjicavanje broja zapisa
	 * 
	 * $qb->limit(100);
	 * @param integer $limit Broj zapisa
	 * @return \App\Classes\QueryBuilder $this
	 */
	public function limit(int $limit)
	{
		$this->limit = $limit;
		return $this;
	}

	private function compileWheres()
	{
		if (!$this->wheres) {
			return '';
		}
		$wheres = (array)$this->wheres;
		$sql = " WHERE ";
		$first = array_shift($wheres);
		$sql .= "{$first[1]}";
		foreach ($wheres as $where) {
			$sql .= "{$where[0]}{$where[1]}";
		}
		return $sql;
	}

	/**
	 * Pravi ORDER BY deo upita
	 */
	protected function compileOrders()
	{
		if (!$this->orders) {
			return '';
		}
		$orders = implode(', ', $this->orders);
		$sql = " ORDER BY {$orders}";
		return $sql;
	}

	/**
	 * Pravi LIMIT deo upita
	 */
	protected function compileLimit()
	{
		if (!$this->limit) {
			return '';
		}
		return " LIMIT {$this->limit}";
	}

	/**
	 * INSERT - upis podataka
	 * 
	 * $qb->insert('broj', 'godina', 'naziv');
	 * @link https://mariadb.com/kb/en/library/insert-on-duplicate-key-update/ ON DUPLICATE KEY UPDATE
	 * @param array $columns Kolone koje se upisuju
	 * @throws \Exception ako je zapocet neki drugi tip upita
	 */
	public function insert(array $columns)
	{
		if ($this->type) {
			throw new \Exception('Vec je zapocet neki drugi tip upita!');
		}
		$this->type = $this::INSERT;
		$cols = array_map('trim', $columns);
		$pars = [];
		foreach ($cols as $c) {
			$pars[] = ':' . $c;
		}
		$this->columns = $cols;
		$this->parameters = $pars;
	}

	/**
	 * Pravi INSERT parametrizovan sql upit
	 */
	protected function compileInsert()
	{
		$sql = "INSERT INTO {$this->table} (";
		$cols = implode(', ', $this->columns);
		$pars = implode(', ', $this->parameters);
		$sql .= "{$cols}) VALUES ({$pars});";
		return $sql;
	}

	/**
	 * UPDATE - izmena podataka
	 * 
	 * $qb->update()->where()->orderBy()->limit();
	 * @param array $columns Kolone koje se menjaju
	 * @throws \Exception ako je zapocet neki drugi tip upita
	 */
	public function update(array $columns)
	{
		if ($this->type) {
			throw new \Exception('Vec je zapocet neki drugi tip upita!');
		}
		$this->type = $this::UPDATE;
		$cols = array_map('trim', $columns);
		$pars = [];
		foreach ($cols as $c) {
			$pars[] = ':' . $c;
		}
		$this->columns = $cols;
		$this->parameters = $pars;
		return $this;
	}

	/**
	 * Pravi UPDATE parametrizovan sql upit
	 * @return string
	 */
	protected function compileUpdate()
	{
		if (count($this->columns) !== count($this->parameters)) {
			throw new \Exception('Broj kolona i parametara mora da bude isti!');
		}
		$sql = "UPDATE {$this->table} SET ";
		$pairs = [];
		foreach ($this->columns as $col) {
			if (in_array(':' . $col, $this->parameters))
				$pairs[] = "{$col} = :{$col}";
		}
		$set = implode(', ', $pairs);
		$sql .= "{$set}";
		$sql .= $this->compileWheres();
		$sql .= $this->compileOrders();
		$sql .= $this->compileLimit();
		$sql .= ";";
		return $sql;
	}

	/**
	 * DELETE - brisanje podataka
	 * 
	 * $qb->delete(1); // id
	 * $qb->delete()->where('broj = :broj')->orderBy('godina ASC')->limit(1);
	 * @param array $columns Kolone koje se menjaju
	 * @throws \Exception ako je zapocet neki drugi tip upita
	 * @return \App\Classes\QueryBuilder $this
	 */
	public function delete(bool $id = false)
	{
		if ($this->type) {
			throw new \Exception('Vec je zapocet neki drugi tip upita!');
		}
		$this->type = $this::DELETE;
		if ($id) {
			$this->wheres = ['id'];
			$this->parameters = [':id'];
		}
		return $this;
	}

	/**
	 * Pravi DELETE parametrizovan sql upit
	 * @return string
	 */
	protected function compileDelete()
	{
		$sql = "DELETE FROM {$this->table}";
		$sql .= $this->compileWheres();
		$sql .= $this->compileOrders();
		$sql .= $this->compileLimit();
		$sql .= ";";
		return $sql;
	}







	protected function compileSQL()
	{
		$sql = "";
		switch ($this->type) {
			case $this::SELECT:
				$sql = $this->compileSelect();
				break;
			case $this::INSERT:
				$sql = $this->compileInsert();
				break;
			case $this::UPDATE:
				$sql = $this->compileUpdate();
				break;
			case $this::DELETE:
				$sql = $this->compileDelete();
				break;
			default:
				throw new \Exception('Greska pri kompajliranju upita');
				break;
		}
		$this->sql = $sql;
	}

	public function params()
	{
		return $this->parameters;
	}

	public function sql()
	{
		$this->compileSQL();
		return $this->sql;
	}

}
