<?php

namespace App\Models;

class Korisnik extends Model
{
	protected $table = 'korisnici';
	protected $relations = [
		// HAS_ONE ima iste parametre
		'predmeti' => [
			'type' => self::HAS_MANY,
			'model'=> '\App\Models\Predmet',
			'model_fk'=> 'korisnik_id',
		],
	];
}
