<?php

namespace App\Controllers;

use App\Models\Predmet;
use App\Models\Korisnik;
use App\Models\Komintent;
use App\Models\VrstaUpisnika;
use App\Classes\QueryBuilder;
use App\Classes\Validator;

class HomeController extends Controller
{
	public function getHome($request, $response)
	{

		dd("");
		$this->render($response, 'home.twig', compact('rezultat'));
	}

	public function getPagination($request, $response, $args)
	{
		$query = [];
		parse_str($request->getUri()->getQuery(), $query);
		$page = isset($query['page']) ? (int)$query['page'] : 1;

		$model = new Predmet;
		$predmeti = $model->select()
			->where([['vrsta_upisnika_id', '=', 1]]) // 1-9
			->orderBy(['id ASC'])
			->paginate($page);
		$this->render($response, 'pagination.twig', compact('predmeti'));
	}

	public function postAjaxPagination($request, $response, $args)
	{
		$params = $request->getParams();
		$params['csrf_name'] = $this->csrf->getTokenName();
		$params['csrf_value'] = $this->csrf->getTokenValue();

		// rows : 10,
		// order : [0, false],
		// start : 101 ,
		// search : "256/2018"

		return json_encode($params);
	}

}
