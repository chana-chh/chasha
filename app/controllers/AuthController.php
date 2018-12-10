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
        $data = $request->getParams();
        $validation_rules = [
            'ime' => [
                'required' => true,
                'minlen' => 5,
                'alnum' => true,
            ],
            'korisnicko_ime' => [
                'required' => true,
                'minlen' => 3,
                'maxlen' => 50,
                'alnum' => true,
            ],
            'lozinka' => [
                'required' => true,
                'minlen' => 6,
            ],
            'potvrda_lozinke' => [
                'match_field' => 'lozinka',
            ],
        ];

        $this->validator->validate($data, $validation_rules);
        dd($this->validator, true);

        return $response->withRedirect($this->router->pathFor('prijava'));
    }

    public function getPrijava($request, $response)
    {
        $this->render($response, 'auth/prijava.twig');
    }

    public function postPrijava($request, $response)
    {
        $ok = $this->auth->login($request->getParam('username'), $request->getParam('password'));
        if ($ok) {
            $this->flash->addMessage('success', 'Al si se logovo. Svaka chas!');
            return $response->withRedirect($this->router->pathFor('pocetna'));
        } else {
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
