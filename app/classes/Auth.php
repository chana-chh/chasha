<?php

/**
 * Auth - klasa za autentikaciju (autorizaciju)
 *
 * Autentikacija i autorizacija korisnika
 *
 * @version v 0.0.1
 * @author ChaSha
 * @copyright Copyright (c) 2019, ChaSha
 */
namespace App\Classes;

use App\Models\Korisnik;

/**
 * Auth korisnika
 *
 * @author ChaSha
 */
class Auth
{
	/**
	 * Korisnik
	 * @var App\Models\Korisnik
	 */
	private $user;

	private $model;

	/**
	 * Konstruktor
	 *
	 * Instancira praznog korisnika
	 */
	public function __construct()
	{
		$this->model = new Korisnik();
	}

	/**
	 * Prijava korisnika
	 *
	 * Pokusava prijavu korisnika na osnovu korisnickog imena i lozinke
	 *
	 * @param string $username Korisnicko ime
	 * @param string $password Lozinka
	 * @return boolean Da li je korisnik uspesno prijavljen
	 */
	public function login($username, $password)
	{
		$user = $this->model->findByUsername($username);
		if (!$user) {
			return false;
		}
		if ($this->checkPassword($password, $user->lozinka)) {
			$_SESSION['user'] = $user->id;
			return true;
		}
		return false;
	}

	/**
	 * Da li je korisnik prijavljen
	 *
	 * @return boolean Da li je korisnik prijavljen ili ne
	 */
	public function isLoggedIn()
	{
		return isset($_SESSION['user']);
	}

	/**
	 * Vraca model prijavljenog korisnika
	 *
	 * @return App\Models\Korisnik | null
	 */
	public function user()
	{
		if (isset($_SESSION['user'])) {
			return $this->model->find((int)$_SESSION['user']);
		}
		return null;
	}

	/**
	 * Odjavljivanje korisnika
	 */
	public function logout()
	{
		unset($_SESSION['user']);
	}

	/**
	 * Provera lozinke u odnosu na hash lozinke
	 *
	 * @return boolean Da li uneta lozinka odgovara hash-u iz baze
	 */
	public function checkPassword($password, $hash)
	{
		return password_verify($password, $hash);
	}

}
