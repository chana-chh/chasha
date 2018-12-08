<?php

namespace App\Classes;

abstract class Model
{

	protected $db;
	protected $table;
	protected $alias;
	protected $pk = 'id';
	protected $model;
	protected $qb;
	protected $params = null;

	public function __construct($qb = null)
	{
		$this->db = new Db();
		if ($qb) {
			if ($qb->table() !== $this->table) {
				throw new \Exception("Tabela iz QueryBuilder-a ne odgovara tabeli iz Model-a");
			}
			$this->qb = $qb;
		} else {
			$this->qb = new QueryBuilder($this->table);
		}
		$this->model = get_class($this);
	}

	public function getTable()
	{
		return $this->table;
	}

	public function getPrimaryKey()
	{
		return $this->pk;
	}

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

	protected function query($sql, $params = null)
	{
		return $this->db->qry($sql, $params, $this->model);
	}

	protected function fetch($sql, $params = null)
	{
		return $this->db->sel($sql, $params, $this->model);
	}

	public function lastId()
	{
		return $this->db->lastId();
	}

	public function lastCount()
	{
		return $this->db->lastCount();
	}

	public function lastError()
	{
		return $this->db->lastError();
	}

	public function lastQuery()
	{
		return $this->db->lastQuery();
	}

	public function select(...$columns)
	{
		$this->qb->select($columns);
		return $this;
	}

	public function distinct()
	{
		$this->qb->distinct();
		return $this;
	}

	public function join($join_table, $this_table_key, $join_table_key)
	{
		$this->qb->join($join_table, $this_table_key, $join_table_key);
		return $this;
	}

	public function leftJoin($join_table, $this_table_key, $join_table_key)
	{
		$this->qb->leftJoin($join_table, $this_table_key, $join_table_key);
		return $this;
	}

	public function rightJoin($join_table, $this_table_key, $join_table_key)
	{
		$this->qb->rightJoin($join_table, $this_table_key, $join_table_key);
		return $this;
	}

	public function fullJoin($join_table, $this_table_key, $join_table_key)
	{
		$this->qb->fullJoin($join_table, $this_table_key, $join_table_key);
		return $this;
	}

	public function where(...$wheres)
	{
		$this->qb->where(...$wheres);
		return $this;
	}

	public function orWhere(...$wheres)
	{
		$this->qb->orWhere(...$wheres);
		return $this;
	}

	public function groupBy($groups)
	{
		$this->qb->groupBy($groups);
		return $this;
	}

	public function having(...$havings)
	{
		$this->qb->having(...$havings);
		return $this;
	}

	public function orHaving(...$havings)
	{
		$this->qb->orHaving(...$havings);
		return $this;
	}

	public function orderBy($orders)
	{
		$this->qb->orderBy($orders);
		return $this;
	}

	public function limit($limit)
	{
		$this->qb->limit($limit);
		return $this;
	}

	public function offset($offset)
	{
		$this->qb->offset($offset);
		return $this;
	}

	public function getSql()
	{
		return $this->qb->sql();
	}

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

	// Za $this->query
	public function get()
	{
		return $this->fetch($this->qb->sql(), $this->params);
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

	public function insertUpdate($data)
	{
		// Isto kao insert asmo u sql dodqti
		// ON DUPLICATE KEY UPDATE opis = opis; ???: Kad ovo primeniti izasto nikad
	}

	// TODO: Prepraviti na QueryBuilder
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

	// TODO: Prepraviti na QueryBuilder
	public function delete($where)
	{
		list($column, $operator, $value) = $where;
		$sql = "DELETE FROM `{$this->table}` WHERE `{$column}` {$operator} :where_{$column};";
		$params = [":where_{$column}" => $value];
		return Db::qry($sql, $params);
	}

	// TODO: Prepraviti na QueryBuilder
	public function deleteId(int $id)
	{
		$sql = "DELETE FROM `{$this->table}` WHERE `{$this->pk}` = :id";
		$params = [':id' => $id];
		return Db::qry($sql, $params);
	}


	public function run()
	{
		return $this->query($this->qb->sql(), $this->params);
	}

	public function enumOrSetList($column)
	{
		$sql = "SELECT DATA_TYPE, COLUMN_TYPE FROM INFORMATION_SCHEMA.COLUMNS WHERE `TABLE_NAME` = :table AND `COLUMN_NAME` = :column;";
		$params = [':table' => $this->table, ':column' => $column];
		$result = $this->db->sel($sql, $params);
		if ($result['DATA_TYPE'] === 'enum' || $result['DATA_TYPE'] === 'set') {
			$list = explode(",", str_replace("'", "", substr($result['COLUMN_TYPE'], 5, (strlen($result['COLUMN_TYPE']) - 6))));
			if (is_array($list) && !empty($list)) {
				return $list;
			}
		} else {
			return false;
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

}
