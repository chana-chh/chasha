<?php

namespace App\Classes;

class QueryBuilder
{

	private $distinct = false;
	private $columns;
	private $values;
	private $type = 'START'; // SELECT, INSERT, UPDATE, DELETE
	private $table;
	private $joins;
	private $wheres;
	private $groups;
	private $havings;
	private $orders;
	private $limit;
	private $offset;
	private $operators = ['=', '<>', '>', '<', '>=', '<=', 'BETWEEN', 'LIKE', 'IN'];
	private $sql = '';

	/**
	 * new QueryBuilder("tabelica", "tbl")
	 */
	public function __construct($table)
	{
		$this->table = $table;
	}

	/**
	 * select('komintent.naziv AS ime', 'korisnik.email')
	 */
	public function select($columns = [])
	{
		if ($this->type !== 'SELECT' && $this->type !== 'START') {
			throw new \Exception("Nece da moze i 'SELECT' i '{$this->type}'");
		}
		$this->type = 'SELECT';
		$cols = is_array($columns) ? $columns : func_get_args();
		// dd($cols);
		$cols = array_map('trim', $cols);
		$this->columns = empty($cols) ? ['*'] : array_merge((array)$this->columns, $cols);
		return $this;
	}

	/**
	 * distinct()
	 */
	public function distinct()
	{
		if ($this->type !== 'SELECT' && $this->type !== 'START') {
			throw new \Exception("Nece da moze 'DISTINCT' sa '{$this->type}'");
		}
		$this->type = 'SELECT';
		$this->distinct = true;
		return $this;
	}

	/**
	 * INNER JOIN (samo gde su isti u obe tabele)
	 */
	public function join($join_table, $this_table_key, $join_table_key)
	{
		if ($this->type !== 'SELECT' && $this->type !== 'START') {
			throw new \Exception("Nece da moze 'JOIN' sa '{$this->type}'");
		}
		$this->type = 'SELECT';
		$join_table = trim($join_table);
		$this_table_key = trim($this_table_key);
		$join_table_key = trim($join_table_key);
		$join = " JOIN {$join_table} ON {$this->table}.{$this_table_key} = {$join_table}.{$join_table_key}";
		$this->joins = array_merge((array)$this->joins, [$join]);
		return $this;
	}

	/**
	 * LEFT JOIN (svi iz leve i odgovarajuci iz desne tabele)
	 */
	public function leftJoin($join_table, $this_table_key, $join_table_key)
	{
		if ($this->type !== 'SELECT' && $this->type !== 'START') {
			throw new \Exception("Nece da moze 'JOIN' sa '{$this->type}'");
		}
		$this->type = 'SELECT';
		$join_table = trim($join_table);
		$this_table_key = trim($this_table_key);
		$join_table_key = trim($join_table_key);
		$join = " LEFT JOIN {$join_table} ON {$this->table}.{$this_table_key} = {$join_table}.{$join_table_key}";
		$this->joins = array_merge((array)$this->joins, [$join]);
		return $this;
	}

	/**
	 * RIGHT JOIN (svi iz desne i odgovarajuci iz leve tabele)
	 */
	public function rightJoin($join_table, $this_table_key, $join_table_key)
	{
		if ($this->type !== 'SELECT' && $this->type !== 'START') {
			throw new \Exception("Nece da moze 'JOIN' sa '{$this->type}'");
		}
		$this->type = 'SELECT';
		$join_table = trim($join_table);
		$this_table_key = trim($this_table_key);
		$join_table_key = trim($join_table_key);
		$join = " RIGHT JOIN {$join_table} ON {$this->table}.{$this_table_key} = {$join_table}.{$join_table_key}";
		$this->joins = array_merge((array)$this->joins, [$join]);
		return $this;
	}

	/**
	 * FULL JOIN (svi iz obe tabele)
	 */
	public function fullJoin($join_table, $this_table_key, $join_table_key)
	{
		if ($this->type !== 'SELECT' && $this->type !== 'START') {
			throw new \Exception("Nece da moze 'JOIN' sa '{$this->type}'");
		}
		$this->type = 'SELECT';
		$join_table = trim($join_table);
		$this_table_key = trim($this_table_key);
		$join_table_key = trim($join_table_key);
		$join = " FULL JOIN {$join_table} ON {$this->table}.{$this_table_key} = {$join_table}.{$join_table_key}";
		$this->joins = array_merge((array)$this->joins, [$join]);
		return $this;
	}

	/**
	 * where("id = 35")
	 */
	public function where(...$wheres)
	{
		foreach ($wheres as $where) {
			$this->wheres = array_merge((array)$this->wheres, [[' AND ', trim($where)]]);
		}
		return $this;
	}

	/**
	 * orWhere("name LIKE '%chana%'")
	 */
	public function orWhere(...$wheres)
	{
		foreach ($wheres as $where) {
			$this->wheres = array_merge((array)$this->wheres, [[' OR ', trim($where)]]);
		}
		return $this;
	}

	/**
	 * groupBy('prezime ASC', 'ime DESC')
	 */
	public function groupBy($groups)
	{
		$this->groups = array_map('trim', func_get_args());
		return $this;
	}

	/**
	 * having("suma >= 20000", "korisnik.email LIKE '%chana%'")
	 */
	public function having(...$havings)
	{
		foreach ($havings as $having) {
			$this->havings = array_merge((array)$this->havings, [[' AND ', trim($having)]]);
		}
		return $this;
	}

	/**
	 * orHaving("ime = 'Nenad'")
	 */
	public function orHaving(...$havings)
	{
		foreach ($havings as $having) {
			$this->havings = array_merge((array)$this->havings, [[' OR ', trim($having)]]);
		}
		return $this;
	}

	/**
	 * orderBy('tabelica.prezime ASC', 'tabelica.ime DESC')
	 */
	public function orderBy($orders)
	{
		$ord = array_map('trim', func_get_args());
		$this->orders = array_merge((array)$this->orders, $ord);
		return $this;
	}

	/**
	 * limit(50)
	 */
	public function limit($limit)
	{
		$this->limit = $limit;
		return $this;
	}

	/**
	 * offset(100)
	 */
	public function offset($offset)
	{
		$this->offset = $offset;
		return $this;
	}

	public function insert($columns, array $values = null)
	{
		if ($this->type !== 'INSERT') {
			$this->type = 'INSERT';
			$this->columns = null;
		}
		$cols = is_array($columns) ? $columns : func_get_args();
		$cols = array_map('trim', $cols);
		$this->values = $values;
		$this->columns = array_merge((array)$this->columns, $cols);
		// return $this;
	}

	public function update()
	{
	}

	public function delete()
	{
	}

	private function compileSql()
	{
		$sql = "";
		switch ($this->type) {
			case 'SELECT':
				$sql .= $this->compileSelect();
				$sql .= " FROM {$this->table}";
				$sql .= $this->compileJoins();
				$sql .= $this->compileWheres();
				$sql .= $this->compileGroups();
				$sql .= $this->compileHavings();
				$sql .= $this->compileOrders();
				$sql .= $this->compileLimit();
				$sql .= $this->compileOffset();
				$sql .= ';';
				$this->sql = $sql;
				break;
			case 'INSERT':
				$sql = $this->compileInsert();
				$this->sql = $sql;
				break;
			default:
				throw new \Exception("Koji je ovo tip upita???");

				break;
		}
	}

	private function compileInsert()
	{
		$sql = "INSERT INTO {$this->table} (";
		$columns = implode(', ', $this->columns);
		$sql .= "{$columns}) VALUES (";
		$values = [];
		if ($this->values) {
			foreach ($this->values as $value) {
				$values[] = is_string($value) ? "'" . $value . "'" : $value;
			}
		} else {
			foreach ($this->columns as $value) {
				$values[] = ":" . $value;
			}
		}
		$vals = implode(', ', $values);
		$sql .= "{$vals});";
		return $sql;
	}

	private function compileSelect()
	{
		$sql = "SELECT ";
		if ($this->distinct) {
			$sql .= "DISTINCT ";
		}
		$columns = $this->columns ? implode(', ', $this->columns) : '*';
		$sql .= $columns;
		return $sql;
	}

	private function compileJoins()
	{
		if (!$this->joins) {
			return '';
		}
		$joins = implode(' ', $this->joins);
		return $joins;
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

	private function compileGroups()
	{
		if (!$this->groups) {
			return '';
		}
		$groups = implode(', ', $this->groups);
		$sql = " GROUP BY {$groups}";
		return $sql;
	}

	private function compileHavings()
	{
		if (!$this->havings) {
			return '';
		}
		$havings = (array)$this->havings;
		$sql = " HAVING ";
		$first = array_shift($havings);
		$sql .= "{$first[1]}";
		foreach ($havings as $having) {
			$sql .= "{$having[0]}{$having[1]}";
		}
		$this->sql = $sql;
		return $sql;
	}

	private function compileOrders()
	{
		if (!$this->orders) {
			return '';
		}
		$orders = implode(', ', $this->orders);
		$sql = " ORDER BY {$orders}";
		return $sql;
	}

	private function compileLimit()
	{
		if (!$this->limit) {
			return '';
		}
		return " LIMIT {$this->limit}";
	}

	private function compileOffset()
	{
		if (!$this->offset) {
			return '';
		}
		return " OFFSET {$this->offset}";
	}

	public function reset()
	{
		$this->distinct = false;
		$this->columns = null;
		$this->joins = null;
		$this->wheres = null;
		$this->groups = null;
		$this->havings = null;
		$this->orders = null;
		$this->limit = null;
		$this->offset = null;
		$this->sql = '';
		$this->type = 'START';
		return $this;
	}

	public function table()
	{
		return $this->table;
	}

	public function sql()
	{
		$this->compileSQL();
		return $this->sql;
	}

	public function __toString()
	{
		return $this->sql();
	}
}
