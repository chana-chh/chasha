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
		$qb = new QueryBuilder('predmeti');

		$qb->select()
			->where([['id', '>=', 500]]);

		// SELECT
		// $qb->select(['id', 'broj', 'godina']);
		// $qb->leftJoin('vezana', 'vezana_id', 'id');
		// $qb->join('druga', 'druga_id', 'id');
		// $qb->where([['id', '>=', 500]])->orWhere([['broj', '<', 1000]]);
		// $qb->where([['godina', 'BETWEEN', [2000, 2010]]])->orWhere([['napomena', '=', 'chana']]);
		// $qb->groupBy(['id DESC']);
		// $qb->having([['SUM(broj)', '>', 5623]])->orHaving([['godina', '>', 2017]]);
		// $qb->having([['godina', 'NOT BETWEEN', [2000, 2010]]])->orHaving([['broj', 'IN', [222, 333, 444, 555]]]);
		// $qb->orderBy(['godina DESC']);
		// $qb->limit(100);
		// $qb->offset(500);

		// dd($qb->tes());

		// INSERT
		// $qb->insert([
		// 	'broj' => 123,
		// 	'godina' => 2018,
		// ]);

		// UPDATE
		// $qb->update([
		// 	'broj' => 123,
		// 	'godina' => 2018,
		// ])->where([['id','=',3]])->orderBy(['broj DESC'])->limit(1);

		// DELETE
		// $qb->delete(123);
		// $qb->delete()->where([['id', '=', 3]])->orderBy(['broj DESC'])->limit(1);

		$params = $qb->getParams();
		$sql1 = $qb->getSql();
		$sql2 = $qb->getSqlWithParams();

		$this->render($response, 'home.twig', compact('params', 'sql1', 'sql2', 'rezultat'));
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
