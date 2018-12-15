<?php

namespace App\Middlewares;

/**
 * Pusta samo prijavljene korisnike
 */
class AuthMiddleware extends Middleware
{
	public function __invoke($request, $response, $next)
	{
		if (!$this->auth->isLoggedIn()) {
			$this->flash->addMessage('warning', 'Samo za prijavljene korisnike');
			return $response->withRedirect($this->router->pathFor('prijava'));
		}
		return $next($request, $response);
	}
}
