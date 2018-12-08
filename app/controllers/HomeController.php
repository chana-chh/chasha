<?php

namespace App\Controllers;

use App\Models\Predmet;
use App\Models\Korisnik;
use App\Classes\QueryBuilder;
use App\Classes\ClosureTest;
use App\Classes\CTT;

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
		];

		// SELECT - test
		// $qb->select();

		// INSERT - test
		// $qb->insert($columns);

		// UPDATE - test
		$qb->update($columns)->where([['broj','>=']])->orderBy(['broj'])->limit(2);
		/*
			Pojavljuje se isti parametar iz update i iz where
		*/

		// DELETE - test
		// $qb->delete()->where([['broj', '=']])->orderBy(['broj'])->limit(2);

		$sql = $qb->getSql();
		$params = $qb->getParams();

		$this->render($response, 'home.twig', compact('params', 'sql'));
	}

}
