<?php

namespace App\Middlewares;

/**
 * Pusta samo korisnike koji nisu prijavljeni
 */
class GuestMiddleware extends Middleware
{
	public function __invoke($request, $response, $next)
	{
		if ($this->auth->isLoggedIn()) {
			return $response->withRedirect($this->router->pathFor('pocetna'));
		}
		return $next($request, $response);
	}
}
