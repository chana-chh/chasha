<?php

namespace App\Models;

class Predmet extends Model
{
	protected $table = 'predmeti';

	public function broj()
	{
		return $this->vrstaUpisnika()->slovo . '-' . $this->broj_predmeta . '/' . $this->godina_predmeta;
	}

	public function vrstaUpisnika()
	{
		return $this->belongsTo('App\Models\VrstaUpisnika', 'vrsta_upisnika_id');
	}
}
