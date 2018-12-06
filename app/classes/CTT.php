<?php

namespace App\Classes;

class CTT extends ClosureTest
{

	public static function __callStatic($method, $args)
	{
		return static::$method(...$args);
	}
}
