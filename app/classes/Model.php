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

use App\Classes\Paginator;

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
	 * @var \App\Classes\Db
	 */
	protected $db;

	/**
	 * Naziv tabele modela
	 * @var string
	 */
	protected $table;

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
	 * Konfiguracija za paginaciju
	 * @var array
	 */
	protected $pagination_config;

	/**
	 * Konstruktor
	 *
	 * @param \App\Classes\QueryBuilder Query builder
	 * @throws \Exception Ako tabele u QueryBuilder-u i Model-u nisu iste
	 */
	public function __construct()
	{
		$this->db = Db::instance();
		$this->pagination_config = Config::get('pagination');
		$this->model = get_class($this);
	}

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
	public function run(string $sql, array $params = null)
	{
		return Db::run($sql, $params);
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
	public function fetch(string $sql, array $params = null, string $model = null)
	{
		$model = ($model === null) ? $this->model : $model;
		return Db::fetch($sql, $params, $model);
	}

	/**
	 * Vraca sve zapise iz tabele (sortirane)
	 *
	 * @param string $sort_column Naziv kolone za sortiranje
	 * @param string $sort Ncin sortiranja
	 * @return array|\App\Classes\Model Niz modela ili jedan model
	 */
	public function all(string $sort_column = null, $sort = 'ASC')
	{
		$order_by = '';
		$params = null;
		$sort = ($sort === 'DESC') ? $sort : 'ASC';
		if ($sort_column !== null && !empty(trim($sort_column))) {
			$order_by = " ORDER BY :{$sort_column} {$sort}";
			$params = [":{$sort_column}" => $sort_column];
		}
		$sql = "SELECT * FROM {$this->table}{$order_by};";
		return $this->fetch($sql, $params);
	}

	/**
	 * Pronalazi red po PK
	 *
	 * @param $id Vrednost PK reda koji se trazi
	 * @return \App\Classes\Model
	 */
	public function find(int $id)
	{
		$sql = "SELECT * FROM {$this->table} WHERE {$this->pk} = :id LIMIT 1;";
		$params = [":id" => $id];
		return $this->fetch($sql, $params) === [] ? null : $this->fetch($sql, $params)[0];
	}

	/**
	 * Vraca SELECT sa navedenim kolonama
	 */
	public function select(array $columns)
	{
		$cols = implode(', ', $columns);
		$sql = "SELECT {$cols} FROM {$this->table};";
		return $this->fetch($sql);
	}

	/**
	 * Vraca broj redova u tabeli
	 */
	public function countTable()
	{
		$sql = "SELECT COUNT(*) AS table_count FROM {$this->table}";
		return (int)$this->fetch($sql)[0]->table_count;
	}

	/**
	 * Upisuje red u tabelu
	 */
	public function insert(array $data)
	{
		foreach ($data as $key => $value) {
			$cols[] = $key;
			$pars[] = ':' . $key;
			$vals[] = $value;
		}
		$params = array_combine($pars, $vals);
		$c = implode(', ', $cols);
		$v = implode(', ', $pars);
		$sql = "INSERT INTO `{$this->table}` ({$c}) VALUES ({$v});";
		return $this->run($sql, $params);
	}

	/**
	 * Menja red u tabeli
	 */
	public function update(array $data, int $id)
	{
		foreach ($data as $key => $value) {
			$cols[] = $key;
			$pars[] = ':' . $key;
			$vals[] = $value;
		}
		$params = array_combine($pars, $vals);
		$params[":{$this->pk}"] = $id;
		$cv = array_combine($cols, $pars);
		$c = '';
		foreach ($cv as $key => $val) {
			$c .= ", {$key} = {$val}";
		}
		$c = ltrim($c, ', ');
		$sql = "UPDATE `{$this->table}` SET {$c} WHERE {$this->pk} = :{$this->pk};";
		return $this->run($sql, $params);
	}

	/**
	 * Brise red u tabeli sa prosledjenim ID
	 */
	public function deleteOne(int $id)
	{
		$sql = "DELETE FROM {$this->table} WHERE {$this->pk} = :{$this->pk}";
		$params = [":{$this->pk}" => $id];
		return $this->run($sql, $params);
	}

	/**
	 * Brise redove u tabeli na osnovu prosledjenog kriterijuma
	 */
	public function delete(array $where)
	{
		list($column, $operator, $value) = $where;
		$sql = "DELETE FROM {$this->table} WHERE {$column} {$operator} :{$column};";
		$params = [":{$column}" => $value];
		return $this->run($sql, $params);
	}

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
				WHERE TABLE_NAME = :tn AND COLUMN_NAME = :cn;";
		$params = [':tn' => $this->table, ':cn' => $column];
		$result = $this->fetch($sql, $params);
		if ($result->DATA_TYPE === 'enum' || $result->DATA_TYPE === 'set') {
			$list = explode(
				",",
				str_replace(
					"'",
					"",
					substr($result->COLUMN_TYPE, 5, (strlen($result->COLUMN_TYPE) - 6))
				)
			);
			if (is_array($list) && !empty($list)) {
				return $list;
			}
		} else {
			return null;
		}
	}

	/**
	 * Vraca podatke i linkove za stranicu
	 *
	 * @param integer $page Broj stranice
	 * @param integer $perpage Broj redova na stranici
	 * @return array podaci + linkovi
	 */
	public function paginate(int $page, string $sql = null, array $params = null, int $perpage = null, int $span = null)
	{
		$sql = ($sql !== null) ? $sql : "SELECT * FROM {$this->table};";
		$data = $this->pageData($page, $sql, $params, $perpage);
		$links = $this->pageLinks($page, $perpage, $span);
		return ['data' => $data, 'links' => $links];
	}

	/**
	 * Vraca podatke za stranicu
	 *
	 * @param integer $page Broj stranice
	 * @param integer $perpage Broj redova na stranici
	 * @return array \App\Classes\Model Niz modela sa podacima
	 */
	protected function pageData(int $page, string $sql, array $params = null, int $perpage = null)
	{
		$sql = str_replace('SELECT', 'SELECT SQL_CALC_FOUND_ROWS', $sql);
		if ($perpage === null) {
			$perpage = $this->pagination_config['per_page'];
		}
		$limit = $perpage;
		$offset = ($page - 1) * $perpage;
		$sql = rtrim($sql, ';');
		$sql .= " LIMIT {$limit} OFFSET {$offset};";
		$data = $this->fetch($sql, $params);
		return $data;
	}

	/**
	 * Vraca broj redova poslednjeg upita bez limita
	 */
	protected function foundRows()
	{
		$sql = "SELECT FOUND_ROWS() AS count;";
		return (int)$this->fetch($sql)[0]->count;
	}

	/**
	 * Vraca linkove i dr. za paginaciju
	 */
	protected function pageLinks(int $page, int $perpage = null)
	{
		$links = [];
		$links['current_page'] = $page;
		if ($perpage === null) {
			$perpage = $this->pagination_config['per_page'];
		}
		$links['per_page'] = $perpage;
		$span = $this->pagination_config['page_span'];
		$count = $this->foundRows();
		$links['total_rows'] = $count;
		$u = Config::getContainer('request')->getUri();
		$url = $u->getBaseUrl() . '/' . $u->getPath();
		$links['url'] = $url;
		$pages = (int)ceil($count / $perpage);
		$links['total_pages'] = $pages;
		$full_span = (($span * 2 + 1) > $pages) ? $pages : $span * 2 + 1;
		$prev = ($page > 2) ? $page - 1 : 1;
		$links['prev_page'] = $prev;
		$next = ($page < $pages) ? $page + 1 : $pages;
		$links['next_page'] = $next;
		$start = $page - $span;
		$end = $page + $span;
		if ($page <= $span + 1) {
			$start = 1;
			$end = $full_span;
		}
		if ($page >= $pages - $span) {
			$start = $pages - $span * 2;
			$end = $pages;
		}
		if ($full_span >= $pages) {
			$start = 1;
			$end = $pages;
		}
		$disabled_begin = ($page === 1) ? " pgn-btn-disabled" : "";
		$disabled_end = ($page === $pages) ? " pgn-btn-disabled" : "";
		$zapis_od = (($page - 1) * $perpage) + 1;
		$zapis_do = ($zapis_od + $perpage) - 1;
		$zapis_do = $zapis_do >= $count ? $count : $zapis_do;
		$links['row_from'] = $zapis_od;
		$links['row_to'] = $zapis_do;

		$buttons = "";
		$buttons .= '<a class="pgn-btn pgn-first' . $disabled_begin . '" href="' . $url . '?page=1" tabindex="-1">1</a>';
		$buttons .= '<a class="pgn-btn' . $disabled_begin . '" href="' . $url . '?page=' . $prev . '" tabindex="-1"><i class="fas fa-angle-left"></i></a>';
		for ($i = $start; $i <= $end; $i++) {
			$current = '';
			if ($page === $i) {
				$current = " pgn-btn-disabled pgn-cur-btn";
			}
			$buttons .= '<a class="pgn-btn' . $current . '" href="' . $url . '?page=' . $i . '" tabindex="-1">' . $i . '</a>';
		}
		$buttons .= '<a class="pgn-btn' . $disabled_end . '" href="' . $url . '?page=' . $next . '" tabindex="-1"><i class="fas fa-angle-right"></i></a>';
		$buttons .= '<a class="pgn-btn pgn-last' . $disabled_end . '" href="' . $url . '?page=' . $pages . '" tabindex="-1">' . $pages . '</a>';
		$links['buttons'] = $buttons;
		$goto = '<select class="pgn-goto" name="pgn-goto" id="pgn-goto">';
		for ($i = 1; $i <= $pages; $i++) {
			$selected = '';
			if ($page === $i) {
				$selected = ' selected';
			}
			$goto .= '<option value="' . $url . '?page=' . $i . '"' . $selected . '>' . $i . '</option>';
		}
		$goto .= '</select>';
		$links['select'] = $goto;

		return $links;
	}

	/**
	 * Vraca Model povezan kao has one
	 *
	 * one to one (vraca dete)
	 *
	 * @param string $model_class Klasa deteta
	 * @param string $foreign_table_fk
	 * @return \App\Classes\Model Instanca deteta
	 */
	public function hasOne($model_class, $foreign_table_fk)
	{
		$m = new $model_class();
		$sql = "SELECT * FROM {$m->getTable()} WHERE {$foreign_table_fk} = :{$foreign_table_fk};";
		$pk = $this->getPrimaryKey();
		$params = [":{$foreign_table_fk}" => $this->$pk];
		$result = $this->fetch($sql, $params, $model_class);
		return $result[0];
	}

	/**
	 * Vraca Modele povezane kao has many
	 *
	 * one to many (vraca decu)
	 *
	 * @param string $model_class Klasa deteta
	 * @param string $foreign_table_fk
	 * @return array \App\Classes\Model Niz instanci dece
	 */
	public function hasMany($model_class, $foreign_table_fk)
	{
		$m = new $model_class();
		$sql = "SELECT * FROM {$m->getTable()} WHERE {$foreign_table_fk} = :{$foreign_table_fk};";
		$pk = $this->getPrimaryKey();
		$params = [":{$foreign_table_fk}" => $this->$pk];
		$result = $this->fetch($sql, $params, $model_class);
		return $result;
	}

	/**
	 * Vraca Model povezan kao belongs to
	 *
	 * one to one (vraca roditelja)
	 * one to many (vraca roditelja)
	 *
	 * @param string $model_class Klasa roditelja
	 * @param string $this_table_fk
	 * @return \App\Classes\Model Instanca roditelja
	 */
	public function belongsTo($model_class, $this_table_fk)
	{
		$m = new $model_class();
		$sql = "SELECT * FROM {$m->getTable()} WHERE {$m->getPrimaryKey()} = :{$m->getPrimaryKey()};";
		$params = [":{$m->getPrimaryKey()}" => $this->$this_table_fk];
		$result = $this->fetch($sql, $params, $model_class);
		return $result[0];
	}

	/**
	 * Vraca Modele povezane kao belongs to many
	 *
	 * many to many (vraca drugu stranu pivot tabele)
	 *
	 * @param string $model_class Klasa druge strane
	 * @param string $pivot_table Naziv pivot tabele
	 * @param string $pt_this_table_fk FK ove strane u pivot tabeli
	 * @param string $pt_foreign_table_fk FK druge strane u pivot tabeli
	 * @return array \App\Classes\Model Niz instanci druge strane
	 */
	public function belongsToMany($model_class, $pivot_table, $pt_this_table_fk, $pt_foreign_table_fk)
	{
		$m = new $model_class();
		$tbl = $m->getTable();
		$pk = $this->getPrimaryKey();
		$params = [":{$pk}" => $this->$pk];
		$sql = "SELECT {$tbl}.* FROM {$tbl} JOIN {$pivot_table} ON {$tbl}.{$m->getPrimaryKey()} = {$pivot_table}.{$pt_foreign_table_fk} WHERE {$pivot_table}.{$pt_this_table_fk} = :{$pk};";
		$result = $this->fetch($sql, $params, $model_class);
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
		return Db::getLastInsertedId();
	}

	/**
	 * Vraca poslednji broj redova tabele
	 *
	 * @return integer
	 */
	public function getLastCount()
	{
		return Db::getLastRowCount();
	}

	/**
	 * Vraca poslednju PDO gresku
	 *
	 * @return string
	 */
	public function getLastError()
	{
		return Db::getLastError();
	}

	/**
	 * Vraca poslednji izvrseni PDO upit
	 *
	 * @return string
	 */
	public function getLastQuery()
	{
		return Db::getLastQuery();
	}

	/**
	 * Vraca kolone tabele
	 *
	 * @return array
	 */
	public function getTableFields()
	{
		$sql = "SHOW COLUMNS FROM {$this->table};";
		return $this->fetch($sql);
	}

	/**
	 * Vraca kljuceve tabele
	 *
	 * @return array
	 */
	public function getTableKeys()
	{
		$sql = "SHOW KEYS FROM {$this->table};";
		return $this->fetch($sql);
	}

}
