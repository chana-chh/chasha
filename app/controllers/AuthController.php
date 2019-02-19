<?php

namespace App\Controllers;

use App\Classes\Auth;
use App\Models\Korisnik;
use App\Classes\Validator;

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
                'unique' => 'korisnici.username', // tabela.kolona
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
        // TODO: Upisati novog korisnika
        die();
        if ($this->validator->hasErrors()) {
            $this->flash->addMessage('danger', 'Doslo je do greske prilikom registracije korisnika!');
            return $response->withRedirect($this->router->pathFor('registracija'));
        } else {
            $this->flash->addMessage('success', 'Al si se upiso u usere. Svaka chas!');
            return $response->withRedirect($this->router->pathFor('prijava'));
        }
    }

    public function getPrijava($request, $response)
    {
        $this->render($response, 'auth/prijava.twig');
    }

    public function postPrijava($request, $response)
    {
        $ok = $this->auth->login($request->getParam('korisnicko_ime'), $request->getParam('lozinka'));
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
