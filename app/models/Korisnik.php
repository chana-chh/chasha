<?php

namespace App\Models;

use App\Classes\Model;

class Korisnik extends Model
{
	protected $table = 'korisnici';

	public function findByUsername(string $username)
	{
		$sql = "SELECT * FROM {$this->table} WHERE korisnicko_ime = :kime LIMIT 1;";
		$params = [':kime' => $username];
		return $this->fetch($sql, $params)[0];
	}
}
