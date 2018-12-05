<?php

namespace App\Controllers;

class Controller {

	protected $container;

	function __construct($container) {
		$this->container = $container;
	}

	public function __get($property) {
		if($this->container->get($property)) {
			return $this->container->get($property);
		}
	}

	protected function render($response, $template, $vars = []) {
		$this->container->view->render($response, $template, $vars);
	}
}
