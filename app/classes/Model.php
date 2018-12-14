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
	 * @var \App\Classes\Db
	 */
	protected $db;

	/**
	 * Query builder
	 * @var \App\Classes\QueryBuilder
	 */
	protected $qb;

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
	 * Konfiguracija za model
	 * @var array
	 */
	protected $pagination_config;

	/**
	 * Kolone u tabeli
	 * @var array
	 */
	protected $table_fields;

	/**
	 * Kljucevi u tabeli
	 * @var array
	 */
	protected $table_keys;

	/**
	 * Originalne vrednosti polja
	 * @var array
	 */
	protected $original_instance_fields;

	/**
	 * KOnacne vrednosti polja
	 * @var array
	 */
	protected $instance_fields;

	/**
	 * Konstruktor
	 *
	 * @param \App\Classes\QueryBuilder Query builder
	 * @throws \Exception Ako tabele u QueryBuilder-u i Model-u nisu iste
	 */
	public function __construct($qb = null)
	{
		$this->db = Config::$container['db'];

		$this->pagination_config = Config::$config['pagination'];

		if ($qb) {
			if ($qb->getTable() !== $this->table) {
				throw new \Exception('Tabela iz QueryBuilder-a ne odgovara tabeli iz Model-a');
			}
			$this->qb = $qb;
		} else {
			$this->qb = new QueryBuilder($this->table);
		}
		$this->model = get_class($this);
		$this->original_instance_fields = $this->extractInstanceFields();
		$this->extractTableFields();
		$this->extractTableKeys();
	}

	/**
	 * Vraca polja sa vrednostima instance modela
	 */
	protected function extractInstanceFields()
	{
		$fields = (new \ReflectionObject($this))->getProperties(\ReflectionProperty::IS_PUBLIC);
		$result = [];
		foreach ($fields as $field) {
			$result[$field->name] = $this->{$field->name};
		}
		return $result;
	}

	/**
	 * Popunjava nazive i svojstva kolona u tabeli
	 */
	protected function extractTableFields()
	{
		$columns = $this->db->sel("SHOW COLUMNS FROM {$this->table};");
		foreach ($columns as $column) {
			$this->table_fields[$column->Field]['type'] = $column->Type;
			$this->table_fields[$column->Field]['key'] = $column->Key;
			$this->table_fields[$column->Field]['default'] = $column->Default;
		}
	}

	/**
	 * Popunjava nazive i svojstva kljuceva u tabeli
	 */
	protected function extractTableKeys()
	{
		$keys = $this->db->sel("SHOW KEYS FROM {$this->table};");
		foreach ($keys as $key) {
			$this->table_keys[$key->Key_name][$key->Seq_in_index]['column'] = $key->Column_name;
			$this->table_keys[$key->Key_name][$key->Seq_in_index]['unique'] = $key->Non_unique === 0 ? true : false;
			$this->table_keys[$key->Key_name][$key->Seq_in_index]['colation'] = $key->Collation;
			$this->table_keys[$key->Key_name][$key->Seq_in_index]['cardinality'] = $key->Cardinality;
		}
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
	protected function query(string $sql, array $params = null)
	{
		return $this->db->qry($sql, $params);
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
	protected function fetch(string $sql, array $params = null)
	{
		return $this->db->sel($sql, $params, $this->model, [$this->qb]);
	}

	/**
	 * Izvrsava sirovi upit
	 *
	 * @param string $sql SQL izraz
	 * @param array $params Parametri za parametrizovani upit
	 * @return array|\PDOStatement Niz rezultata (instanci Model-a) upita ili PDOStatement
	 */
	public function raw(string $sql, array $params = null)
	{
		if (strpos($sql, 'SELECT') !== false) {
			return $this->fetch($sql, $params);
		} else {
			return $this->query($sql, $params);
		}
	}

	/**
	 * Vraca sve zapise iz tabele (sortirane)
	 *
	 * @param string $sort_column Naziv kolone za sortiranje
	 * @param string $sort Ncin sortiranja
	 * @return array|\App\Classes\Model Niz modela ili jedan model
	 */
	public function all($sort_column = null, $sort = 'ASC')
	{
		$order_by = !empty(trim($sort_column)) ? ["{$sort_column} {$sort}"] : null;
		$this->qb->reset();
		$order_by ? $this->qb->select()->orderBy($order_by) : $this->qb->select();
		return $this->get();
	}

	/**
	 * Pronalazi red po PK
	 *
	 * @param $id Vrednost PK reda koji se trazi
	 * @return \App\Classes\Model
	 */
	public function find(int $id)
	{
		$this->qb->reset();
		$this->qb->select()->where([["{$this->pk}", '=', (int)$id]]);
		return $this->get();
	}

	/**
	 * Snima novi ili izmenjeni red
	 *
	 * @throws \Exception Ako je pozvan save na prazan model
	 */
	public function save()
	{
		$this->instance_fields = $this->getInstanceFields();

		if (count($this->original_instance_fields) === 0 && count($this->instance_fields) > 0) {
			$this->qb->reset();
			$this->qb->insert($this->instance_fields);
			$this->run();
			return;
		}
		if (count($this->original_instance_fields) > 0 && count($this->instance_fields) > 0) {
			$dif = [];
			foreach ($this->original_instance_fields as $key => $value) {
				if (isset($this->instance_fields[$key]) && $this->instance_fields[$key] !== $value) {
					$dif[$key] = $this->instance_fields[$key];
				}
			}
			if (!empty($dif)) {
				$this->qb->reset();
				$this->qb->update($dif)->where([["{$this->pk}", '=', $this->{$this->pk}]]);
				$this->run();
			}
			return;
		}
		throw new \Exception('Nije moguce uneti prazan red u tabelu');
	}


	public function delete()
	{
		// TODO: Brisanje povezanih sranja
		$this->qb->reset();
		$this->qb->delete($this->{$this->pk});
		$this->run();
	}

	/**
	 * Kada se nizanje zavrsi ovom metodom se vracaju redovi
	 *
	 * @return array Niz Model-a koji predstavljaju red u tabeli
	 */
	public function get()
	{
		// INFO: Ovo ide za SELECT
		return $this->fetch($this->qb->getSql(), $this->qb->getParams());
	}

	/**
	 * Kada se nizanje zavrsi ovom metodom se izvrsava upit
	 *
	 * @return \PDOStatement
	 */
	public function run()
	{
		// INFO: Ovo ide za INSERT, UPDATE i DELETE
		return $this->query($this->qb->getSql(), $this->qb->getParams());
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
				WHERE TABLE_NAME = ? AND COLUMN_NAME = ?;";
		$params = [1 => $this->table, 2 => $column];
		$result = $this->db->sel($sql, $params);
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
	 * @throws \Exception Ako je vec postavljen limit ili offset
	 */
	public function paginate($page, $perpage = null)
	{
		if ($this->qb->canPaginate()) {
			$data = $this->pageData($page, $perpage);
			$links = $this->pageLinks($page, $perpage);
			return [
				'data' => $data,
				'links' => $links,
			];
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
		$this->qb->calcFoundRows()->limit($perpage)->offset($offset);
		// TODO: novo svojstvo $found_rows_count = $this->foundRows() da se koristi kao total u pageLinks
		$data = $this->get();
		return $data;
	}

	/**
	 * Vraca broj redova poslednjeg upita bez limita
	 */
	protected function foundRows()
	{
		$count = $this->db->sel("SELECT FOUND_ROWS() AS count;");
		return (int)$count->count;
	}

	// FIXME:
	protected function pageLinks($page, $perpage = null)
	{
		$links = [];
		$links['current_page'] = $page;
		if (!$perpage) {
			$perpage = $this->pagination_config['per_page'];
		}
		$links['per_page'] = $perpage;
		$span = $this->pagination_config['page_span'];
		$count = $this->foundRows();
		$links['rows_total'] = $count;
		$uri = Config::$container['request']->getUri();
		$links['uri'] = $uri;
		$pages = (int)ceil($count / $perpage);
		$links['pages_total'] = $pages;
		$full_span = ($span * 2 + 1) > $pages ? $pages : $span * 2 + 1;
		$prev = ($page > 2) ? $page - 1 : 1;
		$next = ($page < $pages) ? $page + 1 : $pages;
		$disabled_begin = ($page === 1) ? " disabled" : "";
		$disabled_end = ($page === $pages) ? " disabled" : "";
		$span_begin = $page - $span;
		$start = $span_begin <= 1 ? 1 : $span_begin;
		$span_end = $page + $span;
		// if ($span_end >= $pages) {
			// 	$end = $pages;
			// 	$start = $end - 2 * $span;
			// 	$start = $start <= 1 ? 1 : $start;
			// } else {
				// 	$end = $span_end;
				// }
		$zapis_od = (($page - 1) * $perpage) + 1;
		$zapis_do = ($zapis_od + $perpage) - 1;
		$zapis_do = $zapis_do >= $count ? $count : $zapis_do;
		// $links = '<a class="pagination-button" href="' . $url . '/1"' . $disabled_begin . '>&lt;&lt;</a>';
		// $links .= '<a class="pagination-button" href="' . $url . '/' . $prev . '"' . $disabled_begin . '>&lt;</a>&nbsp;';
		// for ($i = $start; $i <= $end; $i++) {
		// 	$current = '';
		// 	if ($page === $i) {
		// 		$current = ' current-page';
		// 	}
		// 	$links .= '<a class="pagination-button' . $current . '" href="' . $url . '/' . $i . '">' . $i . '</a>';
		// }
		// $links .= '&nbsp;<a class="pagination-button" href="' . $url . '/' . $next . '"' . $disabled_end . '>&gt;</a>';
		// $links .= '<a class="pagination-button" href="' . $url . '/' . $pages . '"' . $disabled_end . '>&gt;&gt;</a>';
		// $links .= '<br><span class="pagination-info">Strana '
		// 	. $page . ' od ' . $pages
		// 	. ' | Prikazani su zapisi od ' . $zapis_od . ' do ' . $zapis_do
		// 	. ' | Ukupan broj zapisa: ' . $count . '</span>';
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
	public function hasMany($model_class, $foreign_table_fk)
	{
		$m = new $model_class();
		$result = $m->select()->where([[$foreign_table_fk, '=', $this->{$this->pk}]])->get();
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
	 * Vraca parametre za upit upit
	 *
	 * @return string
	 */
	public function getParams()
	{
		return $this->qb->getParams();
	}

	/**
	 * Vraca polja sa originalnim vrednostima instance modela
	 *
	 * @return array
	 */
	public function getOriginalInstanceFields()
	{
		return $this->original_instance_fields;
	}

	/**
	 * Vraca polja sa vrednostima instance modela
	 *
	 * @return array
	 */
	public function getInstanceFields()
	{
		$this->instance_fields = $this->extractInstanceFields();
		return $this->instance_fields;
	}

	/**
	 * Vraca kolone tabele
	 *
	 * @return array
	 */
	public function getTableFields()
	{
		$this->extractTableFields();
		return $this->table_fields;
	}

	/**
	 * Vraca kljuceve tabele
	 *
	 * @return array
	 */
	public function getTableKeys()
	{
		$this->extractTableKeys();
		return $this->table_keys;
	}

	public function __call($method, $arguments)
	{
		if (is_callable([$this->qb, $method])) {
			if ($arguments) {
				$this->qb->$method(...$arguments);
			} else {
				$this->qb->$method();
			}
			return $this;
		}
	}

	public function getSqlWithParams()
	{
		return $this->qb->getSqlWithParams();
	}

}
