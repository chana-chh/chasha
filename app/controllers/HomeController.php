<?php

namespace App\Controllers;

use App\Models\Predmet;
use App\Models\Korisnik;
use App\Classes\QueryBuilder;
use App\Classes\ClosureTest;
use App\Classes\CTT;
use App\Classes\Validator;

class HomeController extends Controller
{
	public function getHome($request, $response)
	{
		$qb = new QueryBuilder('predmeti');

		$columns = [
			'vrsta',
			'broj',
			'godina',
			'naziv',
			'vezana.naziv AS veza',
		];

		// SELECT - test
		$qb->select($columns);
		$qb->leftJoin('vezana', 'vezana_id', 'id');
		$qb->where([['broj', '>']]);
		$qb->groupBy(['premeti.id DESC']);
		$qb->having([['SUM(broj)', '>=']]);
		$qb->orderBy(['godina']);
		$qb->limit(100);
		$qb->offset(300);

		// INSERT - test
		// $qb->insert($columns);

		// UPDATE - test
		// $qb->update($columns)->where([['broj','>=']])->orderBy(['broj'])->limit(2);

		// DELETE - test
		// $qb->delete()->where([['broj', '=']])->orderBy(['broj'])->limit(2);

		$sql = $qb->getSql();
		$params = $qb->getParams();

		$v = new Validator;

		$model = new Predmet($qb);

		// dd($v, true);

		$this->render($response, 'home.twig', compact('params', 'sql'));
	}

}
