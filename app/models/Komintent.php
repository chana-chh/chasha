<?php

namespace App\Models;

use App\Classes\Model;

class Komintent extends Model
{
    protected $table = 's_komintenti';

    public function predmetiTuzilac()
    {
        return $this->belongsToMany('App\Models\Predmet', 'tuzioci', 'komintent_id', 'predmet_id');
    }

    public function predmetiTuzeni()
    {
        return $this->belongsToMany('App\Models\Predmet', 'tuzeni', 'komintent_id', 'predmet_id');
    }
}
