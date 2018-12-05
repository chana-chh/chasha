<?php

namespace App\Models;

class VrstaUpisnika extends Model
{
	protected $table = 's_vrste_upisnika';

	public function predmeti()
	{
		return $this->hasMany('App\Models\Predmet', 'vrsta_upisnika_id');
	}
}
