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
	protected const SELECT = 1;
	protected const INSERT = 2;
	protected const UPDATE = 3;
	protected const DELETE = 4;

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
	 * WHERE za SELECT, UPDATE, DELETE
	 * @var array
	 */
	protected $wheres;

	/**
	 * Validni operatori za WHERE
	 */
	protected $where_operators = [
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
	 * @param string $pk Naziv primarnog kljuca
	 */
	public function setPrimaryKeyName(string $pk = 'id')
	{
		$this->pk = $pk;
	}

	/**
	 * SELECT - odabir podataka
	 *
	 * $qb->select('broj', 'godina', 'naziv AS ime');
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
	 * $qb->select('broj')->addSelect('godina')->addSelect('druga_tabela.id');
	 * @param array $columns Kolone koje se biraju
	 * @throws \Exception ako nije zapocet SELECT upit
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
	 * $qb->insert('broj', 'godina', 'naziv');
	 * @link https://mariadb.com/kb/en/library/insert-on-duplicate-key-update/ ON DUPLICATE KEY UPDATE
	 * @param array $columns Kolone koje se upisuju
	 * @return \App\Classes\QueryBuilder $this
	 * @throws \Exception Ako je zapocet neki drugi tip upita
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
			$pars[] = ':insert_' . $c;
		}
		$this->columns = $cols;
		$this->parameters = $pars;
	}

	/**
	 * UPDATE - izmena podataka
	 *
	 * $qb->update()->where()->orderBy()->limit();
	 * @param array $columns Kolone koje se menjaju
	 * @return \App\Classes\QueryBuilder $this
	 * @throws \Exception Ako je zapocet neki drugi tip upita
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
			$pars[] = ':update_' . $c;
		}
		$this->columns = $cols;
		$this->parameters = $pars;
		return $this;
	}

	/**
	 * DELETE - brisanje podataka
	 *
	 * $qb->delete(true); // id
	 * $qb->delete()->where('broj = :broj')->orderBy('godina ASC')->limit(1);
	 * @param array $columns Kolone koje se menjaju
	 * @return \App\Classes\QueryBuilder $this
	 * @throws \Exception Ako je zapocet neki drugi tip upita
	 */
	public function delete(bool $id = false)
	{
		if ($this->type) {
			throw new \Exception('Vec je zapocet neki drugi tip upita!');
		}
		$this->type = $this::DELETE;
		if ($id) {
			$this->addWhere($this->pk, '=', 'AND');
			$this->parameters = [':' . $this->pk];
			return;
		}
		return $this;
	}

	/**
	 * DISTINCT - upit
	 *
	 * $qb->distinct();
	 * @throws \Exception ako upit nije SELECT
	 * @return \App\Classes\QueryBuilder $this
	 * @throws \Exception Ako nije zapocet SELECT tip upita
	 */
	public function distinct()
	{
		if ($this->type !== $this::SELECT) {
			throw new \Exception('DISTINCT moze samo uz SELECT upit!');
		}
		$this->distinct = true;
		return $this;
	}

	/**
	 * INNER JOIN (samo gde su isti u obe tabele)
	 *
	 * $qb->join('sifarnik','sifra_id', 'id');
	 * @param string $join_table Naziv tabele koja se vezuje
	 * @param string $this_table_key FK u ovoj tabeli koji gadja PK u tabeli koja se vezuje
	 * @param string $join_table_key PK u tabeli koja se vezuje
	 * @return \App\Classes\QueryBuilder $this
	 * @throws \Exception Ako nije zapocet SELECT tip upita
	 */
	public function join($join_table, $this_table_key, $join_table_key)
	{
		if ($this->type !== $this::SELECT) {
			throw new \Exception('JOIN moze samo uz SELECT upit!');
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
	 * @param string $join_table Naziv tabele koja se vezuje
	 * @param string $this_table_key FK u ovoj tabeli koji gadja PK u tabeli koja se vezuje
	 * @param string $join_table_key PK u tabeli koja se vezuje
	 * @return \App\Classes\QueryBuilder $this
	 * @throws \Exception Ako nije zapocet SELECT tip upita
	 */
	public function leftJoin($join_table, $this_table_key, $join_table_key)
	{
		if ($this->type !== $this::SELECT) {
			throw new \Exception('JOIN moze samo uz SELECT upit!');
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
	 * @param string $join_table Naziv tabele koja se vezuje
	 * @param string $this_table_key FK u ovoj tabeli koji gadja PK u tabeli koja se vezuje
	 * @param string $join_table_key PK u tabeli koja se vezuje
	 * @return \App\Classes\QueryBuilder $this
	 * @throws \Exception Ako nije zapocet SELECT tip upita
	 */
	public function rightJoin($join_table, $this_table_key, $join_table_key)
	{
		if ($this->type !== $this::SELECT) {
			throw new \Exception('JOIN moze samo uz SELECT upit!');
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
	 * @param string $join_table Naziv tabele koja se vezuje
	 * @param string $this_table_key FK u ovoj tabeli koji gadja PK u tabeli koja se vezuje
	 * @param string $join_table_key PK u tabeli koja se vezuje
	 * @return \App\Classes\QueryBuilder $this
	 * @throws \Exception Ako nije zapocet SELECT tip upita
	 */
	public function fullJoin($join_table, $this_table_key, $join_table_key)
	{
		if ($this->type !== $this::SELECT) {
			throw new \Exception('JOIN moze samo uz SELECT upit!');
		}
		$join_table = trim($join_table);
		$this_table_key = trim($this_table_key);
		$join_table_key = trim($join_table_key);
		$join = " FULL JOIN {$join_table} ON {$this->table}.{$this_table_key} = {$join_table}.{$join_table_key}";
		$this->joins = array_merge((array)$this->joins, [$join]);
		return $this;
	}

	/**
	 * Dodaje jedan WHERE
	 * @param string $column
	 * @param string $operator
	 * @param string $bool
	 * @throws \Exception Ako operator nije u listi operatora ($this->where_operators) ili je prvi WHERE OR
	 */
	protected function addWhere(string $column, string $operator, string $bool = 'AND')
	{
		$operator = mb_strtoupper($operator);
		if (!$this->wheres && $bool === 'OR') {
			throw new \Exception("Prvi WHERE ne moze da bude OR!");
		}
		if (!in_array($operator, $this->where_operators)) {
			throw new \Exception("Nepostojeci operator [{$operator}]!");
		}
		if ($operator === 'IN' || $operator === 'NOT IN') {
			$this->wheres[] = "{$bool} {$column} {$operator} (:{$column}_in_operator)";
			$this->parameters[] = ":{$column}_in_operator)";
			return;
		}
		if ($operator === 'BETWEEN' || $operator === 'NOT BETWEEN') {
			$this->wheres[] = "{$bool} {$column} {$operator} :{$column}_between_1 AND :{$column}_between_2";
			$this->parameters[] = ":{$column}_between_1";
			$this->parameters[] = ":{$column}_between_2";
			return;
		}
		$this->wheres[] = "{$bool} {$column} {$operator} :{$column}";
		$this->parameters[] = ":{$column}";
	}

	/**
	 * WHERE - filtriranje podataka
	 *
	 * jedan where izgleda ovako [$column, $operator]
	 * @param array $wheres Niz WHERE izraza
	 * @return \App\Classes\QueryBuilder $this
	 * @throws \Exception Ako je zapocet INSERT tip upita
	 */
	public function where(array $wheres)
	{
		if ($this->type === $this::INSERT) {
			throw new \Exception('WHERE ne moze uz INSERT upit!');
		}
		foreach ($wheres as $where) {
			$this->addWhere($where[0], $where[1]);
		}
		return $this;
	}

	/**
	 * WHERE - filtriranje podataka
	 *
	 * jedan where izgleda ovako [$column, $operator]
	 * @param array $wheres Niz WHERE izraza
	 * @return \App\Classes\QueryBuilder $this
	 * @throws \Exception Ako je zapocet INSERT tip upita
	 */
	public function orWhere(array $wheres)
	{
		if ($this->type === $this::INSERT) {
			throw new \Exception('WHERE ne moze uz INSERT upit!');
		}
		foreach ($wheres as $where) {
			$this->addWhere($where[0], $where[1], 'OR');
		}
		return $this;
	}

	/**
	 * GROUP BY - grupisanje podataka
	 *
	 * $qb->groupBy('prezime ASC', 'ime DESC')
	 * @param array $groups Niz sa grupisanjima
	 * @return \App\Classes\QueryBuilder $this
	 * @throws \Exception Ako nije zapocet SELECT tip upita
	 */
	public function groupBy(array $groups)
	{
		if ($this->type !== $this::SELECT) {
			throw new \Exception('GROUP BY moze samo uz SELECT upit!');
		}
		$this->groups = array_map('trim', $groups);
		return $this;
	}

	/**
	 * having("suma >= 20000", "korisnik.email LIKE '%chana%'")
	 */
	public function having(...$havings)
	{
		// FIXME:
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
		// FIXME:
		foreach ($havings as $having) {
			$this->havings = array_merge((array)$this->havings, [[' OR ', trim($having)]]);
		}
		return $this;
	}

	/**
	 * ORDER BY - sortiranje podataka
	 *
	 * $qb->orderBy(['godina DESC', 'broj ASC']);
	 * @param array $orders Niz sortiranja
	 * @return \App\Classes\QueryBuilder $this
	 * @throws \Exception Ako nije zapocet SELECT, UPDATE ili DELETE tip upita
	 */
	public function orderBy(array $orders)
	{
		if ($this->type !== $this::SELECT && $this->type !== $this::UPDATE && $this->type !== $this::DELETE) {
			throw new \Exception('ORDER BY moze samo uz SELECT, UPDATE ili DELETE upit!');
		}
		$this->orders = array_map('trim', $orders);
		return $this;
	}

	/**
	 * LIMIT - ogranjicavanje broja zapisa
	 *
	 * $qb->limit(100);
	 * @param integer $limit Broj zapisa
	 * @return \App\Classes\QueryBuilder $this
	 * @throws \Exception Ako nije zapocet SELECT, UPDATE ili DELETE tip upita
	 */
	public function limit(int $limit)
	{
		if ($this->type !== $this::SELECT && $this->type !== $this::UPDATE && $this->type !== $this::DELETE) {
			throw new \Exception('LIMIT moze samo uz SELECT, UPDATE ili DELETE upit!');
		}
		$this->limit = $limit;
		return $this;
	}

	/**
	 * OFFSET - pomeranje pocetnog zapisa
	 *
	 * $qb->offset(200);
	 * @param integer $offset Broj zapisa koji se preskacu
	 * @return \App\Classes\QueryBuilder $this
	 * @throws \Exception Ako nije zapocet SELECT tip upita
	 */
	public function offset(int $offset)
	{
		if ($this->type !== $this::SELECT) {
			throw new \Exception('OFFSET moze samo uz SELECT upit!');
		}
		$this->offset = $offset;
		return $this;
	}

	/**
	 * Pravi SELECT parametrizovan sql upit
	 */
	protected function compileSelect()
	{
		$sql = "SELECT ";
		if ($this->distinct) {
			$sql .= "DISTINCT ";
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
	 * Pravi UPDATE parametrizovan sql upit
	 * @return string
	 * @throws \Exception Ako je UPDATE cele tabele
	 */
	protected function compileUpdate()
	{
		if ($this->compileWheres() === '' && $this->compileLimit() === '') {
			throw new \Exception('Nije dozvoljeno menjanje cele tabele!');
		}
		$sql = "UPDATE {$this->table} SET ";
		$pairs = [];
		foreach ($this->columns as $col) {
			if (in_array(':update_' . $col, $this->parameters))
				$pairs[] = "{$col} = :update_{$col}";
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
	 * @return string
	 */
	protected function compileHavings()
	{
		// FIXME:
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

	/**
	 * Pravi ORDER BY deo upita
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
	 * @return string
	 */
	protected function compileLimit()
	{
		if (!$this->limit) {
			return '';
		}
		return " LIMIT {$this->limit}";
	}

	/**
	 * Pravi OFFSET deo upita
	 * @return string
	 */
	protected function compileOffset()
	{
		if (!$this->offset) {
			return '';
		}
		return " OFFSET {$this->offset}";
	}

	/**
	 * Pravi konacni upit ($this->sql)
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

	protected function reset()
	{

	}

	/**
	 * Vraca naziv tabele
	 * @return string
	 */
	public function getTable()
	{
		return $this->table;
	}

	/**
	 * Vraca naziv primarnog kljuca
	 * @return string
	 */
	public function getPimaryKeyName()
	{
		return $this->pk;
	}

	/**
	 * Vraca parametre upita
	 * @return array
	 */
	public function getParams()
	{
		return $this->parameters;
	}

	/**
	 * Pravi i vraca konacni parametrizovani upit
	 * @return string
	 */
	public function getSql()
	{
		$this->compileSQL();
		return $this->sql;
	}

}
