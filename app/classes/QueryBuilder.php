<?php

/**
 * QueryBuilder za MySQL
 *
 * Svaki upit mora da pocne sa select(), insert(), update() ili delete()
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
	public const SELECT = 1;
	public const INSERT = 2;
	public const UPDATE = 3;
	public const DELETE = 4;

	/**
	 * Tip upita SELECT, INSERT, UPDATE, DELETE
	 * @var integer
	 */
	protected $type;

	/**
	 * Naziv tabele u db
	 * @var string
	 */
	protected $table;

	/**
	 * Naziv primarnog kljuca
	 * @var string
	 */
	protected $pk;

	/**
	 * Da li je upit DISTINCT
	 * @var boolean
	 */
	protected $distinct = false;

	/**
	 * Da li je upit koristi SQL_CALC_FOUND_ROWS
	 * @var boolean
	 */
	protected $sql_calc_foun_rows = false;

	/**
	 * WHERE za SELECT, UPDATE, DELETE
	 * @var array
	 */
	protected $wheres;

	/**
	 * Validni operatori
	 */
	protected $operators = [
		'=', '<>', '!=', '<', '>', '<=', '>=',
		'LIKE', 'NOT LIKE', 'IN', 'NOT IN', 'BETWEEN', 'NOT BETWEEN'
	];

	/**
	 * JOIN tabele za SELECT
	 * @var array
	 */
	protected $joins;

	/**
	 * GROUP BY za SELECT
	 */
	protected $groups;

	/**
	 * HAVING za SELECT GROUP BY
	 * @var array
	 */
	protected $havings;

	/**
	 * ORDER BY za SELECT, UPDATE, DELETE
	 * @var array
	 */
	protected $orders;

	/**
	 * LIMIT za SELECT, UPDATE, DELETE
	 * @var integer
	 */
	protected $limit;

	/**
	 * OFFSET za SELECT
	 * @var integer
	 */
	protected $offset;

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
	 * SQl izraz - krajnji rezultat QueryBuilder-a
	 * @var string
	 */
	protected $sql = '';

	/**
	 * Konstruktor
	 *
	 * $qb = new QueryBuilder('tabela')
	 *
	 * @param string $table Naziv tabele
	 * @param string $pk Naziv primarnog kljuca
	 */
	public function __construct(string $table, string $pk = 'id')
	{
		$this->table = $table;
		$this->pk = $pk;
	}

	/**
	 * Postavlja naziv primarnog kljuca ako nije 'id'
	 *
	 * @param string $pk Naziv primarnog kljuca
	 */
	public function setPrimaryKeyName(string $pk = 'id')
	{
		$this->pk = $pk;
	}

	/**
	 * SELECT - odabir podataka
	 *
	 * $qb->select(['broj', 'godina', 'naziv AS ime']);
	 *
	 * @param array $columns Kolone koje se biraju
	 * @return \App\Classes\QueryBuilder $this
	 * @throws \Exception Ako je zapocet neki drugi tip upita
	 */
	public function select(array $columns = [])
	{
		if ($this->type) {
			throw new \Exception('Vec je zapocet neki drugi tip upita!');
		}
		$this->type = $this::SELECT;
		$columns = array_map('trim', $columns);
		$this->columns = empty($columns) ? ["{$this->table}.*"] : $columns;
		return $this;
	}

	/**
	 * Dodavanje SELECT-a
	 *
	 * $qb->select(['broj'])->addSelect(['godina'])->addSelect(['druga_tabela.id']);
	 *
	 * @param array $columns Kolone koje se biraju
	 * @return \App\Classes\QueryBuilder $this
	 * @throws \Exception Ako nije zapocet SELECT tip upita
	 */
	public function addSelect(array $columns)
	{
		if ($this->type !== $this::SELECT) {
			throw new \Exception('Nije zapocet SELECT tip upita!');
		}
		$columns = array_map('trim', $columns);
		$this->columns = array_merge((array)$this->columns, $columns);
		return $this;
	}

	/**
	 * INSERT - upis podataka
	 *
	 * $qb->insert(['broj' => 200, 'godina' => 2018, 'naziv' => 'ime']);
	 *
	 * @link https://mariadb.com/kb/en/library/insert-on-duplicate-key-update/ ON DUPLICATE KEY UPDATE
	 * @param array $columns Kolone sa vrednostima koje se upisuju
	 * @return \App\Classes\QueryBuilder $this
	 * @throws \Exception Ako je zapocet neki drugi tip upita
	 */
	public function insert(array $columns)
	{
		if ($this->type) {
			throw new \Exception('Vec je zapocet neki drugi tip upita!');
		}
		$this->type = $this::INSERT;
		$keys = array_map('trim', array_keys($columns));
		$values = array_map('trim', $columns);
		$columns = array_combine($keys, $values);
		$cols = [];
		$pars = [];
		foreach ($columns as $k => $v) {
			$cols[] = $k;
			$pars[] = $v;
		}
		$this->columns = $cols;
		$this->parameters = $pars;
	}

	/**
	 * UPDATE - izmena podataka
	 *
	 * $qb->update(['godina' => 2017])->where([['id', '=', 3]])->orderBy(['broj'])->limit(1);
	 *
	 * @param array $columns Kolone sa vrednostima koje se menjaju
	 * @return \App\Classes\QueryBuilder $this
	 * @throws \Exception Ako je zapocet neki drugi tip upita
	 */
	public function update(array $columns)
	{
		if ($this->type) {
			throw new \Exception('Vec je zapocet neki drugi tip upita!');
		}
		$this->type = $this::UPDATE;

		$keys = array_map('trim', array_keys($columns));
		$values = array_map('trim', $columns);
		$columns = array_combine($keys, $values);
		$cols = [];
		$pars = [];
		foreach ($columns as $k => $v) {
			$cols[] = $k;
			$pars[] = $v;
		}
		$this->columns = $cols;
		$this->parameters = $pars;
		return $this;
	}

	/**
	 * DELETE - brisanje podataka
	 *
	 * $qb->delete(1244); // id
	 * $qb->delete()->where(['broj = :broj'])->orderBy(['godina ASC'])->limit(1);
	 *
	 * @param array $columns Kolone koje se menjaju
	 * @return \App\Classes\QueryBuilder $this
	 * @throws \Exception Ako je zapocet neki drugi tip upita
	 */
	public function delete(int $id = null)
	{
		if ($this->type) {
			throw new \Exception('Vec je zapocet neki drugi tip upita!');
		}
		$this->type = $this::DELETE;
		if ($id !== null) {
			$this->addWhere($this->pk, '=', $id, 'AND');
			return;
		}
		return $this;
	}

	/**
	 * DISTINCT - upit
	 *
	 * $qb->distinct();
	 *
	 * @return \App\Classes\QueryBuilder $this
	 * @throws \Exception Ako nije zapocet SELECT tip upita
	 */
	public function distinct()
	{
		if ($this->type !== $this::SELECT) {
			throw new \Exception('DISTINCT moze samo uz SELECT tip upita!');
		}
		$this->distinct = true;
		return $this;
	}

	/**
	 * DISTINCT - upit
	 *
	 * $qb->distinct();
	 *
	 * @return \App\Classes\QueryBuilder $this
	 * @throws \Exception Ako nije zapocet SELECT tip upita
	 */
	public function calcFoundRows()
	{
		if ($this->type !== $this::SELECT) {
			throw new \Exception('SQL_CALC_FOUND_ROWS moze samo uz SELECT tip upita!');
		}
		$this->sql_calc_foun_rows = true;
		return $this;
	}

	/**
	 * INNER JOIN (samo gde su isti u obe tabele)
	 *
	 * $qb->join('sifarnik','sifra_id', 'id');
	 *
	 * @param string $join_table Naziv tabele koja se vezuje
	 * @param string $this_table_key FK u ovoj tabeli koji gadja PK u tabeli koja se vezuje
	 * @param string $join_table_key PK u tabeli koja se vezuje
	 * @return \App\Classes\QueryBuilder $this
	 * @throws \Exception Ako nije zapocet SELECT tip upita
	 */
	public function join($join_table, $this_table_key, $join_table_key)
	{
		if ($this->type !== $this::SELECT) {
			throw new \Exception('JOIN moze samo uz SELECT tip upita!');
		}
		$join_table = trim($join_table);
		$this_table_key = trim($this_table_key);
		$join_table_key = trim($join_table_key);
		$join = " JOIN {$join_table} ON {$this->table}.{$this_table_key} = {$join_table}.{$join_table_key}";
		$this->joins = array_merge((array)$this->joins, [$join]);
		return $this;
	}

	/**
	 * LEFT JOIN (svi iz leve i odgovarajuci iz desne tabele)
	 *
	 * $qb->leftJoin('sifarnik','sifra_id', 'id');
	 *
	 * @param string $join_table Naziv tabele koja se vezuje
	 * @param string $this_table_key FK u ovoj tabeli koji gadja PK u tabeli koja se vezuje
	 * @param string $join_table_key PK u tabeli koja se vezuje
	 * @return \App\Classes\QueryBuilder $this
	 * @throws \Exception Ako nije zapocet SELECT tip upita
	 */
	public function leftJoin($join_table, $this_table_key, $join_table_key)
	{
		if ($this->type !== $this::SELECT) {
			throw new \Exception('JOIN moze samo uz SELECT tip upita!');
		}
		$join_table = trim($join_table);
		$this_table_key = trim($this_table_key);
		$join_table_key = trim($join_table_key);
		$join = " LEFT JOIN {$join_table} ON {$this->table}.{$this_table_key} = {$join_table}.{$join_table_key}";
		$this->joins = array_merge((array)$this->joins, [$join]);
		return $this;
	}

	/**
	 * RIGHT JOIN (svi iz desne i odgovarajuci iz leve tabele)
	 *
	 * $qb->rightJoin('sifarnik','sifra_id', 'id');
	 *
	 * @param string $join_table Naziv tabele koja se vezuje
	 * @param string $this_table_key FK u ovoj tabeli koji gadja PK u tabeli koja se vezuje
	 * @param string $join_table_key PK u tabeli koja se vezuje
	 * @return \App\Classes\QueryBuilder $this
	 * @throws \Exception Ako nije zapocet SELECT tip upita
	 */
	public function rightJoin($join_table, $this_table_key, $join_table_key)
	{
		if ($this->type !== $this::SELECT) {
			throw new \Exception('JOIN moze samo uz SELECT tip upita!');
		}
		$join_table = trim($join_table);
		$this_table_key = trim($this_table_key);
		$join_table_key = trim($join_table_key);
		$join = " RIGHT JOIN {$join_table} ON {$this->table}.{$this_table_key} = {$join_table}.{$join_table_key}";
		$this->joins = array_merge((array)$this->joins, [$join]);
		return $this;
	}

	/**
	 * FULL JOIN (svi iz obe tabele)
	 *
	 * $qb->rightJoin('sifarnik','sifra_id', 'id');
	 *
	 * @param string $join_table Naziv tabele koja se vezuje
	 * @param string $this_table_key FK u ovoj tabeli koji gadja PK u tabeli koja se vezuje
	 * @param string $join_table_key PK u tabeli koja se vezuje
	 * @return \App\Classes\QueryBuilder $this
	 * @throws \Exception Ako nije zapocet SELECT tip upita
	 */
	public function fullJoin($join_table, $this_table_key, $join_table_key)
	{
		if ($this->type !== $this::SELECT) {
			throw new \Exception('JOIN moze samo uz SELECT tip upita!');
		}
		$join_table = trim($join_table);
		$this_table_key = trim($this_table_key);
		$join_table_key = trim($join_table_key);
		$join = " FULL JOIN {$join_table} ON {$this->table}.{$this_table_key} = {$join_table}.{$join_table_key}";
		$this->joins = array_merge((array)$this->joins, [$join]);
		return $this;
	}

	// TODO: Dodati where i having grupe
	// TODO: Dodati subquery

	/**
	 * Dodaje jedan WHERE
	 *
	 * @param string $column Kolona po kojoj se filtrira
	 * @param string $operator Operator filtriranja kolone
	 * @param mixed $value Vrednost filtriranja kolone
	 * @param string $bool Veznik za WHERE
	 * @throws \Exception Ako operator nije u listi operatora ($this->operators) ili je prvi WHERE OR
	 */
	protected function addWhere(string $column, string $operator, $value, string $bool = 'AND')
	{
		$operator = mb_strtoupper($operator);
		if (!$this->wheres && $bool === 'OR') {
			throw new \Exception("Prvi WHERE ne moze da bude OR!");
		}
		if (!in_array($operator, $this->operators)) {
			throw new \Exception("Nedozvoljeni operator [{$operator}]!");
		}
		if ($operator === 'IN' || $operator === 'NOT IN') {
			$p = count($value);
			$in = "";
			foreach ($value as $k => $v) {
				$in .= "?, ";
				$this->parameters[] = $v;
			}
			$in = rtrim($in, ', ');
			$this->wheres[] = " {$bool} {$column} {$operator} ({$in})";
			return;
		}
		if ($operator === 'BETWEEN' || $operator === 'NOT BETWEEN') {
			$this->wheres[] = " {$bool} {$column} {$operator} ? AND ?";
			$this->parameters[] = $value[0];
			$this->parameters[] = $value[1];
			return;
		}
		$this->wheres[] = " {$bool} {$column} {$operator} ?";
		$this->parameters[] = $value;
	}

	/**
	 * WHERE - filtriranje podataka
	 *
	 * $qb->where([['sifra','=', 23], ['godina', '>=', 2015]]);
	 *
	 * @param array $wheres Niz WHERE izraza
	 * @return \App\Classes\QueryBuilder $this
	 * @throws \Exception Ako je zapocet INSERT tip upita
	 */
	public function where(array $wheres)
	{
		if ($this->type === $this::INSERT) {
			throw new \Exception('WHERE ne moze uz INSERT tip upita!');
		}
		foreach ($wheres as $where) {
			$this->addWhere($where[0], $where[1], $where[2]);
		}
		return $this;
	}

	/**
	 * WHERE - filtriranje podataka
	 *
	 * $qb->orWhere([['sifra','=', 23], ['godina', '>=', 2015]]);
	 *
	 * @param array $wheres Niz WHERE izraza
	 * @return \App\Classes\QueryBuilder $this
	 * @throws \Exception Ako je zapocet INSERT tip upita
	 */
	public function orWhere(array $wheres)
	{
		if ($this->type === $this::INSERT) {
			throw new \Exception('WHERE ne moze uz INSERT tip upita!');
		}
		foreach ($wheres as $where) {
			$this->addWhere($where[0], $where[1], $where[2], 'OR');
		}
		return $this;
	}

	/**
	 * GROUP BY - grupisanje podataka
	 *
	 * $qb->groupBy(['prezime ASC', 'ime DESC']);
	 *
	 * @param array $groups Niz sa grupisanjima
	 * @return \App\Classes\QueryBuilder $this
	 * @throws \Exception Ako nije zapocet SELECT tip upita
	 */
	public function groupBy(array $groups)
	{
		if ($this->type !== $this::SELECT) {
			throw new \Exception('GROUP BY moze samo uz SELECT tip upita!');
		}
		$this->groups = array_map('trim', $groups);
		return $this;
	}

	/**
	 * Dodaje jedan HAVING
	 *
	 * @param string $column Kolona/izraz po kojoj se filtrira
	 * @param string $operator Operator filtriranja kolone/izraza
	 * @param mixed $value Vrednost filtriranja kolone/izraza
	 * @param string $bool Veznik za HAVING
	 * @throws \Exception Ako operator nije u listi operatora ($this->operators) ili je prvi HAVING OR
	 */
	protected function addHaving(string $column, string $operator, $value, string $bool = 'AND')
	{
		$operator = mb_strtoupper($operator);
		if (!$this->havings && $bool === 'OR') {
			throw new \Exception("Prvi HAVING ne moze da bude OR!");
		}
		if (!in_array($operator, $this->operators)) {
			throw new \Exception("Nepostojeci operator [{$operator}]!");
		}
		if ($operator === 'IN' || $operator === 'NOT IN') {
			$p = count($value);
			$in = "";
			foreach ($value as $k => $v) {
				$in .= "?, ";
				$this->parameters[] = $v;
			}
			$in = rtrim($in, ', ');
			$this->havings[] = " {$bool} {$column} {$operator} ({$in})";
			return;
		}
		if ($operator === 'BETWEEN' || $operator === 'NOT BETWEEN') {
			$this->havings[] = " {$bool} {$column} {$operator} ? AND ?";
			$this->parameters[] = $value[0];
			$this->parameters[] = $value[1];
			return;
		}
		$this->havings[] = " {$bool} {$column} {$operator} ?";
		$this->parameters[] = $value;
	}

	/**
	 * HAVING - filtriranje podataka
	 *
	 * $qb->having([['sifra','=', 130], ['godina', '>=', 2000]]);
	 *
	 * @param array $havings Niz HAVING izraza
	 * @return \App\Classes\QueryBuilder $this
	 * @throws \Exception Ako je zapocet upit koji nije SELECT
	 */
	public function having(array $havings)
	{
		if ($this->type !== $this::SELECT) {
			throw new \Exception('HAVING moze samo uz SELECT tip upita!');
		}
		foreach ($havings as $having) {
			$this->addHaving($having[0], $having[1], $having[2]);
		}
		return $this;
	}

	/**
	 * HAVING - filtriranje podataka
	 *
	 * $qb->having([['sifra','=', 130], ['godina', '>=', 2000]]);
	 *
	 * @param array $havings Niz HAVING izraza
	 * @return \App\Classes\QueryBuilder $this
	 * @throws \Exception Ako je zapocet upit koji nije SELECT
	 */
	public function orHaving(array $havings)
	{
		if ($this->type !== $this::SELECT) {
			throw new \Exception('HAVING moze samo uz SELECT tip upita!');
		}
		foreach ($havings as $having) {
			$this->addHaving($having[0], $having[1], $having[2], 'OR');
		}
		return $this;
	}

	/**
	 * ORDER BY - sortiranje podataka
	 *
	 * $qb->orderBy(['godina DESC', 'broj ASC']);
	 *
	 * @param array $orders Niz sortiranja
	 * @return \App\Classes\QueryBuilder $this
	 * @throws \Exception Ako nije zapocet SELECT, UPDATE ili DELETE tip upita
	 */
	public function orderBy(array $orders)
	{
		if ($this->type === $this::INSERT) {
			throw new \Exception('ORDER BY ne moze uz INSERT tip upita!');
		}
		$this->orders = array_map('trim', $orders);
		return $this;
	}

	/**
	 * LIMIT - ogranjicavanje broja zapisa
	 *
	 * $qb->limit(100);
	 *
	 * @param integer $limit Broj zapisa
	 * @return \App\Classes\QueryBuilder $this
	 * @throws \Exception Ako nije zapocet SELECT, UPDATE ili DELETE tip upita
	 */
	public function limit(int $limit)
	{
		if ($this->type === $this::INSERT) {
			throw new \Exception('LIMIT ne moze uz INSERT tip upita!');
		}
		$this->limit = $limit;
		$this->parameters[] = $limit;
		return $this;
	}

	/**
	 * OFFSET - pomeranje pocetnog zapisa
	 *
	 * $qb->offset(200);
	 *
	 * @param integer $offset Broj zapisa koji se preskacu
	 * @return \App\Classes\QueryBuilder $this
	 * @throws \Exception Ako nije zapocet SELECT tip upita
	 */
	public function offset(int $offset)
	{
		if ($this->type !== $this::SELECT) {
			throw new \Exception('OFFSET moze samo uz SELECT tip upita!');
		}
		$this->offset = $offset;
		$this->parameters[] = $offset;
		return $this;
	}

	/**
	 * Pravi SELECT parametrizovan sql upit
	 *
	 * @return string
	 */
	protected function compileSelect()
	{
		$sql = "SELECT ";
		if ($this->distinct) {
			$sql .= "DISTINCT ";
		}
		if ($this->sql_calc_foun_rows) {
			$sql .= "SQL_CALC_FOUND_ROWS ";
		}
		$columns = $this->columns ? implode(', ', $this->columns) : '*';
		$sql .= $columns;
		$sql .= " FROM {$this->table}";
		$sql .= $this->compileJoins();
		$sql .= $this->compileWheres();
		$sql .= $this->compileGroups();
		$sql .= $this->compileHavings();
		$sql .= $this->compileOrders();
		$sql .= $this->compileLimit();
		$sql .= $this->compileOffset();
		$sql .= ";";
		return $sql;
	}

	/**
	 * Pravi INSERT parametrizovan sql upit
	 *
	 * @return string
	 */
	protected function compileInsert()
	{
		$sql = "INSERT INTO {$this->table} (";
		$cols = implode(', ', $this->columns);
		$p = count($this->parameters);
		$pars = rtrim(str_repeat('?, ', $p), ', ');
		$sql .= "{$cols}) VALUES ({$pars});";
		return $sql;
	}

	/**
	 * Pravi UPDATE parametrizovan sql upit
	 *
	 * @return string
	 * @throws \Exception Ako je UPDATE cele tabele
	 */
	protected function compileUpdate()
	{
		if (!$this->wheres && !$this->limit) {
			throw new \Exception('Nije dozvoljeno menjanje cele tabele!');
		}
		$sql = "UPDATE {$this->table} SET ";
		$pairs = [];
		$keys = array_keys($this->parameters);
		foreach ($this->columns as $col) {
			$pairs[] = "{$col} = ?";
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
	 * Pravi DELETE parametrizovan sql upit
	 *
	 * @return string
	 * @throws \Exception Ako je DELETE cele tabele
	 */
	protected function compileDelete()
	{
		if ($this->compileWheres() === '' && $this->compileLimit() === '') {
			throw new \Exception('Nije dozvoljeno brisanje cele tabele!');
		}
		$sql = "DELETE FROM {$this->table}";
		$sql .= $this->compileWheres();
		$sql .= $this->compileOrders();
		$sql .= $this->compileLimit();
		$sql .= ";";
		return $sql;
	}

	/**
	 * Pravi JOIN deo upita
	 *
	 * @return string
	 */
	protected function compileJoins()
	{
		if (!$this->joins) {
			return '';
		}
		$joins = implode('', $this->joins);
		return $joins;
	}

	/**
	 * Pravi WHERE deo upita
	 *
	 * @return string
	 */
	protected function compileWheres()
	{
		if (!$this->wheres) {
			return '';
		}
		$wheres = (array)$this->wheres;
		$sql = " WHERE ";
		$first = ltrim(array_shift($wheres), 'AND ');
		$sql .= "{$first}";
		$rest = implode('', $wheres);
		$sql .= " {$rest}";
		return rtrim($sql);
	}

	/**
	 * Pravi GROUP BY deo upita
	 *
	 * @return string
	 */
	protected function compileGroups()
	{
		if (!$this->groups) {
			return '';
		}
		$groups = implode(', ', $this->groups);
		$sql = " GROUP BY {$groups}";
		return $sql;
	}

	/**
	 * Pravi HAVING deo upita
	 *
	 * @return string
	 */
	protected function compileHavings()
	{
		if (!$this->havings) {
			return '';
		}
		$havings = (array)$this->havings;
		$sql = " HAVING ";
		$first = ltrim(array_shift($havings), 'AND ');
		$sql .= "{$first}";
		$rest = implode('', $havings);
		$sql .= " {$rest}";
		return rtrim($sql);
	}

	/**
	 * Pravi ORDER BY deo upita
	 *
	 * @return string
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
	 *
	 * @return string
	 */
	protected function compileLimit()
	{
		if (!$this->limit) {
			return '';
		}
		return " LIMIT ?";
	}

	/**
	 * Pravi OFFSET deo upita
	 *
	 * @return string
	 */
	protected function compileOffset()
	{
		if (!$this->offset) {
			return '';
		}
		return " OFFSET ?";
	}

	/**
	 * Pravi konacni upit ($this->sql)
	 *
	 * @throws \Exception Ako nije poznat tip upita
	 */
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
				throw new \Exception('Nepoznat tip upita!');
				break;
		}
		$this->sql = $sql;
	}

	/**
	 * Resetuje sve parametre QueryBuildera
	 */
	public function reset()
	{
		$this->type = null;
		$this->distinct = false;
		$this->wheres = null;
		$this->joins = null;
		$this->groups = null;
		$this->havings = null;
		$this->orders = null;
		$this->limit = null;
		$this->offset = null;
		$this->columns = null;
		$this->parameters = null;
		$this->sql = '';
	}

	/**
	 * Vraca tip upita
	 *
	 * @return integer
	 */
	public function getType()
	{
		return $this->type;
	}

	/**
	 * Vraca naziv tabele
	 *
	 * @return string
	 */
	public function getTable()
	{
		return $this->table;
	}

	/**
	 * Vraca naziv primarnog kljuca
	 *
	 * @return string
	 */
	public function getPimaryKeyName()
	{
		return $this->pk;
	}

	/**
	 * Vraca parametre upita
	 *
	 * @return array
	 */
	public function getParams()
	{
		if ($this->parameters && array_key_exists(0, $this->parameters)) {
			$params = $this->parameters;
			$this->parameters = [];
			foreach ($params as $k => $v) {
				$this->parameters[$k + 1] = $v;
			}
		}
		return $this->parameters;
	}

	/**
	 * Pravi i vraca konacni parametrizovani upit
	 *
	 * @return string
	 */
	public function getSql()
	{
		$this->compileSQL();
		return $this->sql;
	}

	public function getSqlWithParams()
	{
		$sql = str_replace('?', '%s', $this->getSql());
		$res = vsprintf($sql, $this->parameters);
		return $res;
	}

	public function canPaginate()
	{
		return !$this->limit && !$this->offset;
	}

	public function getCountSql()
	{
		$qb = clone $this;
		$qb->columns = ['COUNT(*) AS row_count'];
		return $qb;
	}
}
