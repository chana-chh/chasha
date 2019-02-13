<?php

namespace App\Controllers;

use App\Models\Predmet;
use App\Models\Korisnik;
use App\Models\Komintent;
use App\Models\VrstaUpisnika;
use App\Classes\QueryBuilder;
use App\Classes\Validator;
use App\Classes\Auth;

class HomeController extends Controller
{
	public function getHome($request, $response)
	{
		// Za testiranje modela koristiti s_komintenti (id, naziv)

		$this->render($response, 'home.twig', compact('rezultat'));
	}

	public function getPagination($request, $response, $args)
	{
		$query = [];
		parse_str($request->getUri()->getQuery(), $query);
		$page = isset($query['page']) ? (int)$query['page'] : 1;

		$model = new Korisnik;
		$korisnici = $model->paginate($page);
		$this->render($response, 'pagination.twig', compact('korisnici'));
	}

}
