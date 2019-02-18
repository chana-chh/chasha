<?php

namespace App\Models;

use App\Classes\Model;
use DateTime;

class Predmet extends Model
{
	protected $table = 'predmeti';

	public function broj()
	{
		return $this->vrstaUpisnika()->slovo . '-' . $this->broj_predmeta . '/' . $this->godina_predmeta;
	}

	public function datum_tuzbe()
	{
		return DateTime::createFromFormat('Y-m-d', $this->datum_tuzbe)->format('d.m.Y');
	}

	public function vrstaUpisnika()
	{
		return $this->belongsTo('App\Models\VrstaUpisnika', 'vrsta_upisnika_id');

	}
	public function vrstaPredmeta()
	{
		return $this->belongsTo('App\Models\VrstaPredmeta', 'vrsta_predmeta_id');
	}

	public function tuzioci()
	{
		return $this->belongsToMany('App\Models\Komintent', 'tuzioci', 'predmet_id', 'komintent_id');
	}

	public function tuzeni()
	{
		return $this->belongsToMany('App\Models\Komintent', 'tuzeni', 'predmet_id', 'komintent_id');
	}
}
