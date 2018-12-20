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

namespace App\Models;

use App\Classes\Db;
use App\Classes\Paginator;

/**
 * Model
 *
 * @author ChaSha
 * @abstract
 */
abstract class Model
{

	public const HAS_ONE = 0;
	public const HAS_MANY = 1;
	public const BELONGS_TO_ONE = 2;
	public const BELONGS_TO_MANY = 3;

	/**
	 * PDO wrapper
	 * @var \App\Classes\Db
	 */
	protected $db;

	/**
	 * Naziv tabele modela
	 * @var string
	 */
	protected $table = 'predmeti';

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
	 * Konstruktor
	 * 
	 * Postavlja Db (PDO wrapper) i naziv modela
	 */
	public function __construct()
	{
		$this->db = Db::instance();
		$this->model = get_class($this);
	}

	/**
	 * Izvrsava upit
	 *
	 * @param string $sql SQL izraz
	 * @param array $params Parametri za parametrizovani upit
	 * @return boolean Da li je upit uspesno izvrsen
	 */
	public function run(string $sql, array $params = null)
	{
		if (strpos(strtoupper($sql), 'DELETE') !== false
			&& strpos(strtoupper($sql), 'WHERE') === false
			&& strpos(strtoupper($sql), 'LIMIT') === false) {
			throw new \Exception('Nije dozvoljeno brisanje svih redova tabele!');
		}
		return $this->db::run($sql, $params);
	}

	/**
	 * Izvrsava sirovi upit
	 *
	 * @param string $sql SQL izraz
	 * @param array $params Parametri za parametrizovani upit
	 * @return array
	 */
	public function fetch(string $sql, array $params = null)
	{
		return $this->db::fetch($sql, $params);
	}

	/**
	 * Vraca sve zapise iz tabele (sortirane)
	 *
	 * @param string $sort_column Naziv kolone za sortiranje
	 * @param string $sort Nacin sortiranja
	 * @return array
	 */
	public function all($sort_column = null, $sort = 'ASC')
	{
		$order_by = $sort_column && !empty(trim($sort_column)) ? [" {$sort_column} {$sort}"] : "";
		$sql = "SELECT * FROM {$this->table}{$order_by};";
		return $this->fetch($sql);
	}

	/**
	 * Pronalazi red po PK
	 *
	 * @param $id Vrednost PK reda koji se trazi
	 * @return array
	 */
	public function find(int $id)
	{
		$sql = "SELECT * FROM {$this->table} WHERE {$this->pk} = ? LIMIT 1;";
		$data = $this->fetch($sql, [$id]);
		return count($data) === 1 ? $data[0] : $data;
	}

	/**
	 * Dodaje novi red u tabelu
	 * 
	 * @param array $data Asocijativni niz 'naziv_kolone' => 'vrednost'
	 * @return integer PK novog reda
	 */
	public function insert(array $data)
	{
		foreach ($data as $key => $value) {
			$cols[] = $key;
			$vals[] = ":{$key}";
			$params[":{$key}"] = $value;
		}
		$columns = implode(", ", $cols);
		$values = implode(", ", $vals);
		$sql = "INSERT INTO {$this->table} ({$columns}) VALUES ({$values});";
		$this->run($sql, $params);
		return $this->getLastId();
	}

	/**
	 * Menja vrdnosti kolona u redu
	 * 
	 * @param
	 * @return
	 */
	public function updateId(int $id, array $data)
	{
		foreach ($data as $key => $value) {
			$s[] = "{$key} = :{$key}";
			$params[":{$key}"] = $value;
		}
		$set = implode(", ", $s);
		$sql = "UPDATE {$this->table} SET {$set} WHERE {$this->pk} = :{$this->pk};";
		$params[":{$this->pk}"] = $id;
		return $this->run($sql, $params);
	}

	/**
	 * Brise red po PK
	 *
	 * @return boolean Da li je upit uspesno izvrsen
	 */
	public function deleteId(int $id)
	{
		$sql = "DELETE FROM {$this->table} WHERE {$this->pk} = ? LIMIT 1;";
		return $this->run($sql, [$id]);
	}

	/**
	 * Vraca listu vrednosti iz enum ili set kolone
	 *
	 * Za padajuci meini (select)
	 *
	 * @param string $column Enum ili set kolona u tabeli
	 * @return array|null Lista vrednosti ili NULL ako kolona nije enum ili set
	 */
	public function enumOrSetList($column)
	{
		$sql = "SELECT DATA_TYPE, COLUMN_TYPE
				FROM INFORMATION_SCHEMA.COLUMNS
				WHERE TABLE_NAME = ? AND COLUMN_NAME = ?;";
		$params = [$this->table, $column];
		$result = $this->db::fetch($sql, $params);
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
		}
		return null;
	}

	/**
	 * Vraca podatke i linkove za stranicu
	 *
	 * @param integer $page Broj stranice
	 * @param integer $perpage Broj redova na stranici
	 * @return array podaci + linkovi
	 * @throws \Exception Ako je vec postavljen limit ili offset
	 */
	public function paginate($page, $perpage = null)
	{
		if ($this->qb->canPaginate()) {
			$data = $this->pageData($page, $perpage);
			$pagination = $this->pageLinks($page, $perpage);
			return new Paginator($data, $pagination);
		}
		throw new \Exception('Ne moze paginacija kada postoji limit ili offset');
	}

	/**
	 * Vraca podatke za stranicu
	 *
	 * @param integer $page Broj stranice
	 * @param integer $perpage Broj redova na stranici
	 * @return array \App\Classes\Model Niz modela sa podacima
	 * @throws \Exception Ako tip upita nije SELECT
	 */
	protected function pageData($page, $perpage = null)
	{
		if ($this->qb->getType() !== $this->qb::SELECT) {
			throw new \Exception('Paginacija moze samo iz SELECT tip upita');
		}
		if (!$perpage) {
			$perpage = $this->pagination_config['per_page'];
		}
		$offset = ($page - 1) * $perpage;

		$this->qb_rows_count = $this->qb->getCountSql();
		$this->qb->limit($perpage)->offset($offset);
		$data = $this->get();
		return $data;
	}

	/**
	 *
	 */
	protected function pageLinks($page, $perpage = null)
	{
		$links = [];
		$links['current_page'] = $page;
		if (!$perpage) {
			$perpage = $this->pagination_config['per_page'];
		}
		$links['per_page'] = $perpage;
		$span = $this->pagination_config['page_span'];
		$links['span'] = $span;
		$cnt = $this->db->sel($this->qb_rows_count->getSql(), $this->qb_rows_count->getParams());
		$count = (int)$cnt->row_count;
		$links['rows_total'] = $count;
		$u = Config::$container['request']->getUri();
		$uri = $u->getBaseUrl() . '/' . $u->getPath();
		$links['uri'] = $uri;
		$pages = (int)ceil($count / $perpage);
		$links['pages_total'] = $pages;
		$full_span = ($span * 2 + 1) > $pages ? $pages : $span * 2 + 1;
		$links['full_span'] = $full_span;
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
		$links['span_start_page'] = $start;
		$links['span_end_page'] = $end;

		$disabled_begin = ($page === 1) ? " pgn-btn-disabled" : "";
		$disabled_end = ($page === $pages) ? " pgn-btn-disabled" : "";

		$zapis_od = (($page - 1) * $perpage) + 1;
		$zapis_do = ($zapis_od + $perpage) - 1;
		$zapis_do = $zapis_do >= $count ? $count : $zapis_do;

		$links['row_from'] = $zapis_od;
		$links['row_to'] = $zapis_do;

		$buttons = "";
		$buttons .= '<a class="' . $this->pagination_config['css_class'] . $disabled_begin . '" href="' . $uri . '?page=1" tabindex="-1">1</a>';
		$buttons .= '<a class="' . $this->pagination_config['css_class'] . $disabled_begin . '" href="' . $uri . '?page=' . $prev . '" tabindex="-1"><i class="fas fa-angle-left"></i>&lt;</a>';
		for ($i = $start; $i <= $end; $i++) {
			$current = '';
			if ($page === $i) {
				$current = ' pgn-btn-disabled ' . $this->pagination_config['css_current_class'];
			}
			$buttons .= '<a class="' . $this->pagination_config['css_class'] . $current . '" href="' . $uri . '?page=' . $i . '" tabindex="-1">' . $i . '</a>';
		}
		$buttons .= '<a class="' . $this->pagination_config['css_class'] . $disabled_end . '" href="' . $uri . '?page=' . $next . '" tabindex="-1"><i class="fas fa-angle-right"></i>&gt;</a>';
		$buttons .= '<a class="' . $this->pagination_config['css_class'] . $disabled_end . '" href="' . $uri . '?page=' . $pages . '" tabindex="-1">' . $pages . '</a>';
		$links['buttons'] = $buttons;
		$goto = '<select class="pgn-goto" name="pgn-goto" id="pgn-goto">';
		for ($i = 1; $i <= $pages; $i++) {
			$selected = '';
			if ($page === $i) {
				$selected = ' selected';
			}
			$goto .= '<option value="' . $uri . '?page=' . $i . '"' . $selected . '>' . $i . '</option>';
		}
		$goto .= '</select>';
		$links['select'] = $goto;

		return $links;
	}

	/**
	 * // FIXME: 
	 */
	public function getRelationData(array $data, string $relation_name)
	{
		if (!isset($this->relations[$relation_name])) {
			throw new \Exception("U modelu [{$this->model}] nije definisana relacija [{$relation_name}]");
		}

		$r = $this->relations[$relation_name];

		switch ($r['type']) {
			case self::HAS_ONE:
				# code...
				break;
			case self::HAS_MANY:
				return $this->hasMany($data, $r['model'], $r['model_fk']);
				break;
			case self::BELONGS_TO_ONE:
				# code...
				break;
			case self::BELONGS_TO_MANY:
				# code...
				break;

			default:
				# code...
				break;
		}

		dd($relation, true);
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
		$result = $m->select()->where([[$foreign_table_fk, '=', $this->{$this->pk}]])->limit(1)->get();
		return $result;
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
	public function hasMany(array $data, string $model, string $model_fk)
	{
		$m = new $model();
		$sql = "SELECT * FROM {$m->getTable()} WHERE {$model_fk} = {$this->{$this->pk}}";
		dd($sql, true);
		$result = $m->select()->where([[$foreign_table_fk, '=', $this->{$this->pk}]])->get();
		if ($this->getLastCount() === 1) {
			return [$result];
		}
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
		$result = $m->find($this->$this_table_fk);
		return $result;
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
		$result = $m->select()
			->join($pivot_table, $m->getPrimaryKey(), $pt_foreign_table_fk)
			->where([[$pivot_table . '.' . $pt_this_table_fk, '=', $this->{$this->pk}]])
			->get();
		if ($this->getLastCount() === 1) {
			return [$result];
		}
		return $result;
	}

	/**
	 * Vraca nazive i svojstva kolona u tabeli
	 *
	 * @return array
	 */
	protected function getTableFields()
	{
		$result = [];
		$columns = $this->db::fetch("SHOW COLUMNS FROM {$this->table};");
		foreach ($columns as $column) {
			$result[$column->Field]['type'] = $column->Type;
			$result[$column->Field]['key'] = $column->Key;
			$result[$column->Field]['default'] = $column->Default;
		}
		return $result;
	}

	/**
	 * Popunjava nazive i svojstva kljuceva u tabeli
	 *
	 * @return array
	 */
	protected function getTableKeys()
	{
		$result = [];
		$keys = $this->db::fetch("SHOW KEYS FROM {$this->table};");
		foreach ($keys as $key) {
			$result[$key->Key_name][$key->Seq_in_index]['column'] = $key->Column_name;
			$result[$key->Key_name][$key->Seq_in_index]['unique'] = $key->Non_unique === 0 ? true : false;
			$result[$key->Key_name][$key->Seq_in_index]['colation'] = $key->Collation;
			$result[$key->Key_name][$key->Seq_in_index]['cardinality'] = $key->Cardinality;
		}
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
		return $this->db::getLastId();
	}

	/**
	 * Vraca poslednji broj redova tabele
	 *
	 * @return integer
	 */
	public function getLastCount()
	{
		return $this->db::getLastCount();
	}

	/**
	 * Vraca poslednju PDO gresku
	 *
	 * @return string
	 */
	public function getLastError()
	{
		return $this->db::getLastError();
	}

	/**
	 * Vraca poslednji izvrseni PDO upit
	 *
	 * @return string
	 */
	public function getLastQuery()
	{
		return $this->db::getLastQuery();
	}

}
