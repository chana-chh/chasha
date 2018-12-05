<?php

namespace App\Controllers;

use App\Classes\Auth;
use App\Models\Korisnik;

class AuthController extends Controller
{
    public function getRegistracija($request, $response)
    {
        $this->render($response, 'auth/registracija.twig');
    }

    public function postRegistracija($request, $response)
    {
        return $response->withRedirect($this->router->pathFor('prijava'));
    }

    public function getPrijava($request, $response)
    {
        $this->render($response, 'auth/prijava.twig');
    }

    public function postPrijava($request, $response)
    {
        $ok=$this->auth->login($request->getParam('username'), $request->getParam('password'));
        if ($ok) {
            $this->flash->addMessage('success', 'Al si se logovo. Svaka chas!');
            return $response->withRedirect($this->router->pathFor('pocetna'));
        }else{
            $this->flash->addMessage('danger', 'Negde si se zahebo!');
            return $response->withRedirect($this->router->pathFor('prijava'));
        }
    }

    public function getOdjava($request, $response)
    {
        $this->auth->logout();
        return $response->withRedirect($this->router->pathFor('pocetna'));
    }
}
