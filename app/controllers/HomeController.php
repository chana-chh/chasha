<?php

namespace App\Controllers;

use App\Models\Predmet;
use App\Models\Korisnik;
use App\Classes\QueryBuilder;

class HomeController extends Controller
{

	/*	PREDMETI
		SELECT `predmeti`.`id`, `predmeti`.`arhiviran`, `predmeti`.`broj_predmeta`, `predmeti`.`godina_predmeta`,
		`predmeti`.`opis` as opis_predmeta, `predmeti`.`opis_kp`, `predmeti`.`opis_adresa`, `predmeti`.`datum_tuzbe`,
		`s_vrste_upisnika`.`slovo`, `s_vrste_upisnika`.`naziv`,
		`s_vrste_predmeta`.`naziv` as vp_naziv,
		CONCAT(`s_vrste_upisnika`.`slovo`, '-', `predmeti`.`broj_predmeta`, '/',`predmeti`.`godina_predmeta`) as ceo_broj_predmeta,
		CONCAT(`s_referenti`.`ime`, ' ', `s_referenti`.`prezime`) as puno_ime,
		`s_referenti`.`ime`, `s_referenti`.`prezime`,
		`s_sudovi`.`naziv` as sud_naziv,
		GROUP_CONCAT(DISTINCT brojevi_predmeta_sud.broj SEPARATOR ', ') as sudbroj,
		GROUP_CONCAT(DISTINCT `st1_naziv`.`stt1` SEPARATOR ', ') AS `stranka_1`,
		GROUP_CONCAT(DISTINCT `st2_naziv`.`stt2` SEPARATOR ', ') AS `stranka_2`,
		`poslednji`.`opis`,
		`poslednji`.`datum`,
		`poslednji`.`st_naziv`
		FROM  `predmeti`
		JOIN  `s_vrste_upisnika` ON `predmeti`.`vrsta_upisnika_id` = `s_vrste_upisnika`.`id`
		JOIN  `s_vrste_predmeta` ON `predmeti`.`vrsta_predmeta_id` = `s_vrste_predmeta`.`id`
		JOIN  `s_sudovi` ON `predmeti`.`sud_id` = `s_sudovi`.`id`
		JOIN  `s_referenti` ON `predmeti`.`referent_id` = `s_referenti`.`id`
		LEFT JOIN `brojevi_predmeta_sud` ON `predmeti`.`id` = `brojevi_predmeta_sud`.`predmet_id`
		LEFT JOIN (
			select tokovi_predmeta.*, s_statusi.naziv as st_naziv
			from tokovi_predmeta
			inner join (
				select predmet_id, max(datum) as ts
				from tokovi_predmeta
				group by predmet_id
				) t1 on (tokovi_predmeta.predmet_id = t1.predmet_id and tokovi_predmeta.datum = t1.ts)
				join s_statusi on tokovi_predmeta.status_id = s_statusi.id
		) `poslednji` ON `poslednji`.`predmet_id` = `predmeti`.`id`
		LEFT JOIN (
			SELECT `tuzioci`.`predmet_id`, `s_komintenti`.`naziv` AS `stt1` FROM `tuzioci`
			JOIN `s_komintenti` ON `tuzioci`.`komintent_id` = `s_komintenti`.`id`
		) AS `st1_naziv` ON `st1_naziv`.`predmet_id` = `predmeti`.`id`
		LEFT JOIN (
			SELECT `tuzeni`.`predmet_id`, `s_komintenti`.`naziv` AS `stt2` FROM `tuzeni`
			JOIN `s_komintenti` ON `tuzeni`.`komintent_id` = `s_komintenti`.`id`
		) AS `st2_naziv` ON `st2_naziv`.`predmet_id` = `predmeti`.`id` GROUP BY `predmeti`.`id`;
	 */

	/*	TOKOVI
		SELECT predmeti.id, CONCAT(s_vrste_upisnika.slovo, '-', predmeti.broj_predmeta, '/', predmeti.godina_predmeta) AS broj,
		predmeti.datum_tuzbe,
		s_vrste_upisnika.naziv AS vrsta_upisnika, s_vrste_predmeta.naziv AS vrsta_predmeta,
		tokovi.vsd, tokovi.vsp, tokovi.itd, tokovi.itp
		FROM predmeti
		LEFT JOIN s_vrste_upisnika ON predmeti.vrsta_upisnika_id = s_vrste_upisnika.id
		LEFT JOIN s_vrste_predmeta ON predmeti.vrsta_predmeta_id = s_vrste_predmeta.id
		LEFT JOIN (
			SELECT
			tokovi_predmeta.predmet_id,
			SUM(tokovi_predmeta.vrednost_spora_duguje) AS vsd,
			SUM(tokovi_predmeta.vrednost_spora_potrazuje) AS vsp,
			SUM(tokovi_predmeta.iznos_troskova_duguje) AS itd,
			SUM(tokovi_predmeta.iznos_troskova_potrazuje) AS itp
			FROM tokovi_predmeta GROUP BY tokovi_predmeta.predmet_id
		) AS tokovi ON predmeti.id = tokovi.predmet_id
		LEFT JOIN (
			SELECT tuzioci.predmet_id, s_komintenti.naziv
			FROM tuzioci
			JOIN s_komintenti ON s_komintenti.id = tuzioci.komintent_id
		) AS stranka1 ON stranka1.predmet_id = predmeti.id
		LEFT JOIN (
			SELECT tuzeni.predmet_id, s_komintenti.naziv
			FROM tuzeni
			JOIN s_komintenti ON s_komintenti.id = tuzeni.komintent_id
		) AS stranka2 ON stranka2.predmet_id = predmeti.id
		WHERE ...
		GROUP BY id;
	 */
	public function getHome($request, $response)
	{
		$model = new Predmet();
		$predmet = $model->find(5616);
		// $predmeti = $model->all();
		// dd($predmet, true);
		$predmet->arhiviran = 0;
		// $predmet->vrsta_upisnika_id = 4;
		// $predmet->broj_predmeta = 4;
		// $predmet->godina_predmeta = 2020;
		// $predmet->sud_id = 12;
		// $predmet->vrsta_predmeta_id = 80;
		// $predmet->opis = 'opis opis';
		// $predmet->opis_kp = 'KP 222/3 KO Resnik';
		// $predmet->opis_adresa = 'adresica';
		// $predmet->vrednost_tuzbe = 20000.00;
		// $predmet->datum_tuzbe = '2020-01-02';
		// $predmet->referent_id = 2;
		// $predmet->napomena = 'napomenica';
		// $predmet->korisnik_id = 1;

		$predmet->update();
		// $id = $predmet->lastId();
		// $predmeti = $predmet->find($id);
		dd($predmet, true);
		// $sql = $model->getSql();
		$this->render($response, 'home.twig', compact('predmeti', 'sql'));
	}

}
