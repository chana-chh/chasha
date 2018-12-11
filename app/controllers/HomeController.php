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

		// $columns = [
		// 	'vrsta' => 2,
		// 	'broj' => 153,
		// 	'godina' => 2018,
		// 	'naziv' => 'neki naziv',
		// ];

		$columns = [
			'id',
			'vrsta',
			'broj',
			'godina',
			'naziv',
		];

		// SELECT - test
		$qb->select();
		$qb->where([['id', '>', 100]])->where([['vrsta_upisnika_id', '=', 6]]);

		// INSERT - test
		// $qb->insert($columns);

		$in = [
			1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15
		];
		// UPDATE - test
		// $qb->update($columns)->where([['broj', '>=', 500]])->orWhere([['broj', 'IN', $in]])->orderBy(['broj'])->limit(5);

		// DELETE - test
		// $qb->delete(5);//->where([['broj', '=', 200]])->orderBy(['broj'])->limit(2);

		$sql = $qb->getSql();
		$params = $qb->getParams();

		$predmet = new Predmet($qb);
		$tes=$predmet->get();

		// $sql = "DESCRIBE predmeti;";
		// $sql = "SHOW COLUMNS FROM predmeti;";
		// $sql = "SHOW KEYS FROM predmeti;";
		// dd($this->db->sel("select * from predmeti where id = 1"), true);
		// $predmet->fetch("select * from predmeti where id = 1")->extractInstanceFielsds();
		dd($tes, true);

		$this->render($response, 'home.twig', compact('params', 'sql'));
	}

}
