<?php

namespace App\Controllers;

use App\Models\Predmet;
use App\Models\Korisnik;
use App\Models\VrstaUpisnika;
use App\Classes\QueryBuilder;
use App\Classes\ClosureTest;
use App\Classes\CTT;
use App\Classes\Validator;

class HomeController extends Controller
{
	public function getHome($request, $response)
	{
		$qb = new QueryBuilder('predmeti');

		// SELECT
		$qb->select(['id', 'broj', 'godina']);
		$qb->leftJoin('vezana', 'vezana_id', 'id');
		$qb->join('druga', 'druga_id', 'id');
		$qb->where([['id', '>=', 500]])->orWhere([['broj', '<', 1000]]);
		$qb->where([['godina', 'BETWEEN', [2000, 2010]]])->orWhere([['napomena', '=', 'chana']]);
		$qb->groupBy(['id DESC']);
		$qb->having([['SUM(broj)', '>', 5623]])->orHaving([['godina', '>', 2017]]);
		$qb->having([['godina', 'NOT BETWEEN', [2000, 2010]]])->orHaving([['broj', 'IN', [222, 333, 444, 555]]]);
		$qb->orderBy(['godina DESC']);
		$qb->limit(100);
		$qb->offset(500);

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


		$model = new VrstaUpisnika;
		$model->select();
		$model->limit(5);
		$model->offset(1);

		$rezultat = $model->get();

		$params = $qb->getParams();
		$sql1 = $qb->getSql();
		$sql2 = $qb->tes();

		$this->render($response, 'home.twig', compact('params', 'sql1', 'sql2', 'rezultat'));
	}

}
