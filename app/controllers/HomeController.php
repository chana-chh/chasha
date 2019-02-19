<?php

namespace App\Controllers;

use App\Models\Predmet;
use App\Models\Korisnik;

class HomeController extends Controller
{
	public function getHome($request, $response)
	{
		// $model = new Korisnik();
		// $korisnici = $model->all();
		// dd($korisnici, true);
		$this->render($response, 'home.twig', compact('rezultat'));
	}

	public function getPagination($request, $response, $args)
	{
		$query = [];
		parse_str($request->getUri()->getQuery(), $query);
		$page = isset($query['page']) ? (int)$query['page'] : 1;

		$model = new Predmet();

		$sql = "SELECT * FROM predmeti ORDER BY datum_tuzbe DESC;";

		$predmeti = $model->paginate($page, $sql);

		$this->render($response, 'pagination.twig', compact('predmeti'));
	}

}
