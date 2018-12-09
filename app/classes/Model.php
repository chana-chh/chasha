<?php

/**
 * Osnovni model
 *
 * Svaki model mora da nasledi ovu klasu
 *
 * @version v 0.0.1
 * @author ChaSha
 * @copyright Copyright (c) 2019, ChaSha
 */

namespace App\Classes;

/**
 * Model
 *
 * @author ChaSha
 * @abstract
 */
abstract class Model
{

	/**
	 * PDO wrapper
	 * @var App\Classes\Db
	 */
	protected $db;

	/**
	 * Naziv tabele modela
	 * @var string
	 */
	protected $table;
	// protected $alias;

	/**
	 * Primarni kljuc tabele modela
	 * @var string
	 */
	protected $pk = 'id';

	/**
	 * Naziv momdela
	 * @var string
	 */
	protected $model;

	/**
	 * Query builder
	 * @var App\Classes\QueryBuilder
	 */
	protected $qb;

	/**
	 * Vrednosti parametara
	 * @var array
	 */
	protected $params;

	/**
	 * Konfiguracija za model
	 * @var array
	 */
	protected $config = [
		'per_page' => 10,
		'page_span' => 3,
	];

	/**
	 * Konstruktor
	 *
	 * @param App\Classes\QueryBuilder Query builder
	 * @throws \Exception Ako tabele u QueryBuilder-u i Model-u nisu iste
	 */
	public function __construct($qb = null)
	{
		$this->db = new Db;
		if ($qb) {
			if ($qb->getTable() !== $this->table) {
				throw new \Exception('Tabela iz QueryBuilder-a ne odgovara tabeli iz Model-a');
			}
			$this->qb = $qb;
		} else {
			$this->qb = new QueryBuilder($this->table);
		}
		$this->model = get_class($this);
	}



	// ???

	public function setParams(array $params)
	{
		$this->params = $params;
		return $this;
	}

	public function addParams(array $params)
	{
		$this->params = array_merge((array)$this->params, $params);
		return $this;
	}

	protected function extractParams()
	{
		$keys = array_keys(get_object_vars($this));
		$values = array_values(get_object_vars($this));
		$properties = (new \ReflectionObject($this))->getProperties(\ReflectionProperty::IS_PUBLIC);
		$this->params = [];
		foreach ($properties as $prop) {
			$this->params[$prop->name] = $this->{$prop->name};
		}
	}

	// ???



	/**
	 * Izvrsava upit preko PDO
	 *
	 * Za upite koji menjaju podatke u bazi
	 * INSERT, UPDATE, DELETE
	 *
	 * @param string $sql SQL izraz
	 * @param array $params Parametri za parametrizovani upit
	 * @return \PDOStatement
	 */
	protected function query(string $sql, array $params = null)
	{
		return $this->db->qry($sql, $params, $this->model);
	}

	/**
	 * Izvrsava upit preko PDO
	 *
	 * Za upite koji vracaju podatke iz baze
	 * SELECT
	 *
	 * @param string $sql SQL izraz
	 * @param array $params Parametri za parametrizovani upit
	 * @return array Niz rezultata (instanci Model-a) upita
	 */
	protected function fetch($sql, $params = null)
	{
		return $this->db->sel($sql, $params, $this->model);
	}

	/*
	 * METODE ZA PREUZIMANJE PODATAKA
	 */

	// FIXME: Odavde
	public function all($sort_column = null, $sort = 'ASC')
	{
		$order_by = trim($sort_column) ? "{$sort_column} {$sort}" : null;
		$this->qb->reset();
		return $order_by ? $this->orderBy($order_by)->get() : $this->get();
	}

	public function find(int $id)
	{
		$this->qb->reset();
		return $this->where("id = :id")->setParams([':id' => $id])->get();
	}

	public function insert()
	{
		$this->extractParams();
		foreach ($this->params as $key => $value) {
			$cols[] = $key;
			$pars[] = ':' . $key;
			$vals[] = is_string($value) ? "'" . $value . "'" : $value;
		}
		$params = array_combine($pars, $vals);
		$c = implode(', ', $cols);
		$v = implode(', ', $pars);
		$sql = "INSERT INTO {$this->table} ({$c}) VALUES ({$v});";
		return $this->db->qry($sql, $params);
	}

	public function update()
	{
		$this->extractParams();
		$id = $this->params[$this->pk];
		foreach ($this->params as $key => $value) {
			if ($key !== $this->pk) {
				$s[] = $key . ' = :' . $key;
			}
			$pars[] = ':' . $key;
			$vals[] = is_string($value) ? "'" . $value . "'" : $value;
		}
		$set = implode(', ', $s);
		$params = array_combine($pars, $vals);
		$sql = "UPDATE {$this->table} SET {$set} WHERE {$this->pk} = :{$this->pk};";
		return $this->db->qry($sql, $params);
	}

	public function delete($where)
	{
		list($column, $operator, $value) = $where;
		$sql = "DELETE FROM `{$this->table}` WHERE `{$column}` {$operator} :where_{$column};";
		$params = [":where_{$column}" => $value];
		return Db::qry($sql, $params);
	}

	public function deleteId(int $id)
	{
		$sql = "DELETE FROM `{$this->table}` WHERE `{$this->pk}` = :id";
		$params = [':id' => $id];
		return Db::qry($sql, $params);
	}

	public function get()
	{
		return $this->fetch($this->qb->sql(), $this->params);
	}

	public function run()
	{
		return $this->query($this->qb->sql(), $this->params);
	}
	// FIXME: Dovde

	/**
	 * Vraca listu vrednosti iz enum ili set kolone
	 *
	 * Za padajuci meini (<<select>>) sa predefinisanim vrednostima kolone
	 *
	 * @param string $column Enum ili set kolona u tabeli
	 * @return array|null Lista vrednosti ili NULL ako kolona nije enum ili set
	 */
	public function enumOrSetList($column)
	{
		$sql = "SELECT DATA_TYPE, COLUMN_TYPE
				FROM INFORMATION_SCHEMA.COLUMNS
				WHERE `TABLE_NAME` = :table AND `COLUMN_NAME` = :column;";
		$params = [':table' => $this->table, ':column' => $column];
		$result = $this->db->sel($sql, $params);
		if ($result['DATA_TYPE'] === 'enum' || $result['DATA_TYPE'] === 'set') {
			$list = explode(
				",",
				str_replace(
					"'",
					"",
					substr($result['COLUMN_TYPE'], 5, (strlen($result['COLUMN_TYPE']) - 6))
				)
			);
			if (is_array($list) && !empty($list)) {
				return $list;
			}
		} else {
			return null;
		}
	}

	public function pagination($page, $perpage, $span, $sql, $params = null)
	{
		$data = $this->pageData($page, $perpage, $sql, $params);
		$links = $this->pageLinks($page, $perpage, $span);
		return ['data' => $data, 'links' => $links];
	}

	public function pageData($page, $perpage, $sql, $params = null)
	{
		$sql = str_replace('SELECT', 'SELECT SQL_CALC_FOUND_ROWS', $sql);
		$start = ($page - 1) * $perpage;
		$limit = $perpage;
		$offset = $start;
		$sql = rtrim($sql, ';');
		$sql .= " LIMIT {$limit} OFFSET {$offset};";
		$data = $this->query($sql, $params);
		return $data;
	}

	protected function foundRows()
	{
		$count = $this->query("SELECT FOUND_ROWS() AS count;");
		return (int)$count[0]->count;
	}

	public function pageLinks($page, $perpage, $span)
	{
		$count = $this->foundRows();
		$url = App::instance()->router->getCurrentUriName();
		$pages = (int)ceil($count / $perpage);
		$prev = ($page > 2) ? $page - 1 : 1;
		$next = ($page < $pages) ? $page + 1 : $pages;
		$disabled_begin = ($page === 1) ? " disabled" : "";
		$disabled_end = ($page === $pages) ? " disabled" : "";
		$span_begin = $page - $span;
		$start = $span_begin <= 1 ? 1 : $span_begin;
		$span_end = $start + 2 * $span;
		if ($span_end >= $pages) {
			$end = $pages;
			$start = $end - 2 * $span;
			$start = $start <= 1 ? 1 : $start;
		} else {
			$end = $span_end;
		}
		$zapis_od = (($page - 1) * $perpage) + 1;
		$zapis_do = ($zapis_od + $perpage) - 1;
		$zapis_do = $zapis_do >= $count ? $count : $zapis_do;
		$links = '<a class="pagination-button" href="' . $url . '/1"' . $disabled_begin . '>&lt;&lt;</a>';
		$links .= '<a class="pagination-button" href="' . $url . '/' . $prev . '"' . $disabled_begin . '>&lt;</a>&nbsp;';
		for ($i = $start; $i <= $end; $i++) {
			$current = '';
			if ($page === $i) {
				$current = ' current-page';
			}
			$links .= '<a class="pagination-button' . $current . '" href="' . $url . '/' . $i . '">' . $i . '</a>';
		}
		$links .= '&nbsp;<a class="pagination-button" href="' . $url . '/' . $next . '"' . $disabled_end . '>&gt;</a>';
		$links .= '<a class="pagination-button" href="' . $url . '/' . $pages . '"' . $disabled_end . '>&gt;&gt;</a>';
		$links .= '<br><span class="pagination-info">Strana '
			. $page . ' od ' . $pages
			. ' | Prikazani su zapisi od ' . $zapis_od . ' do ' . $zapis_do
			. ' | Ukupan broj zapisa: ' . $count . '</span>';
		return $links;
	}


	/*
	 * RELACIJE
	 */


	public function hasOne($model_class, $foreign_table_fk)
	{
		$m = new $model_class();
		$sql = "SELECT * FROM `{$m->getTable()}` WHERE `{$foreign_table_fk}` = :fk;";
		$pk = $this->getPrimaryKey();
		$params = [':fk' => $this->$pk];
		$result = $this->db->sel($sql, $params, $model_class);
		return $result[0];
	}

	public function belongsTo($model_class, $this_table_fk)
	{
		$m = new $model_class();
		$sql = "SELECT * FROM `{$m->getTable()}` WHERE `{$m->getPrimaryKey()}` = :fk;";
		$params = [':fk' => $this->$this_table_fk];
		$result = $this->db->sel($sql, $params, $model_class);
		return $result;
	}

	public function hasMany($model_class, $foreign_table_fk)
	{
		$m = new $model_class();
		$sql = "SELECT * FROM `{$m->getTable()}` WHERE `{$foreign_table_fk}` = :pk;";
		$pk = $this->getPrimaryKey();
		$params = [':pk' => $this->$pk];
		$result = $this->db->sel($sql, $params, $model_class);
		return $result;
	}

	public function belongsToMany($model_class, $pivot_table, $pt_this_table_fk, $pt_foreign_table_fk)
	{
		$m = new $model_class();
		$tbl = $m->getTable();
		$pk = $this->getPrimaryKey();
		$params = [':pk' => $this->$pk];
		$sql = "SELECT `{$tbl}`.* FROM `{$tbl}` JOIN `{$pivot_table}` ON `{$tbl}`.`{$m->getPrimaryKey()}` = `{$pivot_table}`.`{$pt_foreign_table_fk}` WHERE `{$pivot_table}`.`{$pt_this_table_fk}` = :pk;";
		$result = $this->db->sel($sql, $params, $model_class);
		return $result;
	}

	/**
	 * Vraca naziv tabele Model-a
	 *
	 * @return string
	 */
	public function getTable()
	{
		return $this->table;
	}

	/**
	 * Vraca naziv primarnog kljuca tabele Model-a
	 *
	 * @return string
	 */
	public function getPrimaryKey()
	{
		return $this->pk;
	}

	/**
	 * Vraca poslednji uneti ID
	 *
	 * @return string
	 */
	public function getLastId()
	{
		return $this->db->getLastId();
	}

	/**
	 * Vraca poslednji broj redova tabele
	 *
	 * @return integer
	 */
	public function getLastCount()
	{
		return $this->db->getLastCount();
	}

	/**
	 * Vraca poslednju PDO gresku
	 *
	 * @return string
	 */
	public function getLastError()
	{
		return $this->db->getLastError();
	}

	/**
	 * Vraca poslednji izvrseni PDO upit
	 *
	 * @return string
	 */
	public function getLastQuery()
	{
		return $this->db->getLastQuery();
	}

	/**
	 * Vraca poslednji parametrizovani upit
	 *
	 * @return string
	 */
	public function getSql()
	{
		return $this->qb->getSql();
	}

	/**
	 * Vraca prethodni parametrizovani upit
	 *
	 * @return string
	 */
	public function getLastSql()
	{
		return $this->qb->getLastSql();
	}

}
