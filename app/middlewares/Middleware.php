<?php

namespace App\Middlewares;

class Middleware
{

	protected $container;

	function __construct($container)
	{
		$this->container = $container;
	}

	public function __get($ime)
	{
		if ($this->container->get($ime)) {
			return $this->container->get($ime);
		}
	}
}
