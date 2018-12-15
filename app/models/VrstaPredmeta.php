<?php

namespace App\Models;

use App\Classes\Model;

class VrstaPredmeta extends Model
{
	protected $table = 's_vrste_predmeta';

	public function predmeti()
	{
		return $this->hasMany('App\Models\Predmet', 'vrsta_upisnika_id');
	}
}
