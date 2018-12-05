<?php

namespace App\Middlewares;

class ValidationErrorsMiddleware extends Middleware
{
	public function __invoke($request, $response, $next)
	{
		$errors = null;
		if (isset($_SESSION['errors'])) {
			$errors = $_SESSION['errors'];
			unset($_SESSION['errors']);
		}
		$this->view->getEnvironment()->addGlobal('errors', $errors);
		return $next($request, $response);
	}
}
