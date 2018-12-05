<?php

namespace App\Classes;

use App\Models\Korisnik;

class Auth
{
	private $user;

	public function __construct()
	{
		$this->user = new Korisnik();
	}

	public function login($username, $password)
	{
		$user = $this->user->where("username = :username")->setParams([':username' => $username])->get();
		if (!$user) {
			return false;
		}
		if ($this->checkPassword($password, $user->password)) {
			$_SESSION['user'] = $user->id;
			return true;
		}
		return false;
	}

	public function isLoggedIn()
	{
		return isset($_SESSION['user']);
	}

	public function user()
	{
		if (isset($_SESSION['user'])) {
			return $this->user->find((int)$_SESSION['user']);
		}
		return null;
	}

	public function logout()
	{
		unset($_SESSION['user']);
	}

	public function checkPassword($password, $hash)
	{
		return password_verify($password, $hash);
	}
}
