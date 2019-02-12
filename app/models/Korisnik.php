<?php

namespace App\Models;

use App\Classes\Model;

class Korisnik extends Model
{
	protected $table = 'korisnici';

	public function findByUsername(string $username)
	{
		$sql = "SELECT * FROM {$this->table} WHERE username = :un LIMIT 1;";
		$params = [':un' => $username];
		return $this->fetch($sql, $params)[0];
	}
}
