<?php

namespace App\Classes;

class Paginator
{
	public $data;
	public $pagination;

	public function __construct($data, $pagination)
	{
		$this->data = $data;
		$this->pagination = $pagination;
	}
}
