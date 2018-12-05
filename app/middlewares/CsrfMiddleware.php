<?php

namespace App\Middlewares;

class CsrfMiddleware extends Middleware
{
	public function __invoke($request, $response, $next)
	{
		$csrf = '
		<input type="hidden" name="' . $this->csrf->getTokenNameKey() . '" value="' . $this->csrf->getTokenName() . '">
		<input type="hidden" name="' . $this->csrf->getTokenValueKey() . '" value="' . $this->csrf->getTokenValue() . '">
		';
		$this->view->getEnvironment()->addGlobal('csrf', $csrf);
		return $next($request, $response);
	}
}
