<?php

/**
 * Validator
 *
 * Validator podataka proverava da li podaci odgovaraju zadatim kriterijumima
 *
 * @version v 0.0.1
 * @author ChaSha
 * @copyright Copyright (c) 2019, ChaSha
 */

namespace App\Classes;

/**
 * Validator podataka
 *
 * @author ChaSha
 */
class Validator
{

    /**
     * PDO wrapper
     * @var App\Classes\Db
     */
    protected $db;

    /**
     * Polja (podaci) za proveru
     * @var array
     */
    protected $items;

    /**
     * Niz gresaka validacije
     * @var array
     */
    protected $errors = [];

    /**
     * Raspoloziva pravila
     * @var array
     */
    protected $rules = [
        'required',
        'len',
        'minlen',
        'maxlen',
        'email',
        'alnum', // + space
        'match_field',
        'unique',
        'min',
        'max',
        'equal',
    ];

    /**
     * Poruke za pravila
     * @var array
     */
    protected $messages = [
        'required' => "Polje :field je obavezno",
        'len' => "Polje :field mora da ima tacno :option karaktera",
        'minlen' => "Polje :field mora da ima najmanje :option karaktera",
        'maxlen' => "Polje :field mora da ima najvise :option karaktera",
        'email' => "Polje :field mora da sadrzi ispravnu email adresu",
        'alnum' => "Polje :field sme da sadrzi samo slova i brojeve",
        'match_field' => "Polja :field i :option moraju da budu ista",
        'unique' => "U bazi vec postoji :field sa istom vrednoscu",
        'max' => "Polje :field mora da bude broj ne veci od :option",
        'min' => "Polje :field mora da bude broj ne mani od :option",
        'equal' => "Polje :field mora da bude jednako :option",
    ];

    /**
     * Konstruktor
     *
     * @param App\Classes\Db $db PDO wrapper
     */
    public function __construct()
    {
        $this->db = Db::instance();
    }

    /**
     * Vrsi validaciju podataka prema pravilima
     *
     * @param array $data Niz podataka koji se proveravaju
     * @param array $rules Niz pravila za proveru podataka
     */
    public function validate(array $data, array $rules)
    {
        $data = $this->sanitize($data);
        $this->items = array_map('trim', $this->sanitize($data));
        foreach ($this->items as $item => $value) {
            if (in_array($item, array_keys($rules))) {
                $this->val([
                    'field' => $item,
                    'value' => $value,
                    'rules' => $rules[$item],
                ]);
            }
        }
    }

    /**
     * Validacija jednog podatka na osnovi seta pravila
     *
     * @param array $item Podatak sa setom pravila za validaciju
     */
    protected function val($item)
    {
        $field = $item['field'];
        $value = $item['value'];
        foreach ($item['rules'] as $rule => $option) {
            if (in_array($rule, $this->rules)) {
                if (!call_user_func_array([$this, $rule], [$field, $value, $option])) {
                    $this->errors[$field][] = str_replace(
                        [':field', ':option'],
                        ['[' . ucfirst(str_replace(['-', '_'], ' ', $field)) . ']', '[' . ucfirst($option) . ']'],
                        $this->messages[$rule]
                    );
                }
            }
        }
    }

    /**
     * Pravilo - obavezan podatak
     *
     * @param string $field Naziv podatka
     * @param string $field Vrednost podatka
     * @param mixed $option Vrednost parametra za zadovoljavanje pravila
     */
    protected function required($field, $value, $option)
    {
        return !empty(trim($value));
    }

    /**
     * Pravilo - odredjena duzina
     *
     * @param string $field Naziv podatka
     * @param string $field Vrednost podatka
     * @param mixed $option Vrednost parametra za zadovoljavanje pravila
     */
    protected function len($field, $value, $option)
    {
        return mb_strlen($value, 'UTF-8') === $option;
    }

    /**
     * Pravilo - minimalna duzina
     *
     * @param string $field Naziv podatka
     * @param string $field Vrednost podatka
     * @param mixed $option Vrednost parametra za zadovoljavanje pravila
     */
    protected function minlen($field, $value, $option)
    {
        return mb_strlen($value, 'UTF-8') >= $option;
    }

    /**
     * Pravilo - maksimalna duzina
     *
     * @param string $field Naziv podatka
     * @param string $field Vrednost podatka
     * @param mixed $option Vrednost parametra za zadovoljavanje pravila
     */
    protected function maxlen($field, $value, $option)
    {
        return mb_strlen($value, 'UTF-8') <= $option;
    }

    /**
     * Pravilo - validan email
     *
     * @param string $field Naziv podatka
     * @param string $field Vrednost podatka
     * @param mixed $option Vrednost parametra za zadovoljavanje pravila
     */
    protected function email($field, $value, $option)
    {
        return filter_var($value, FILTER_VALIDATE_EMAIL);
    }

    /**
     * Pravilo - alfanumerik + space
     *
     * @param string $field Naziv podatka
     * @param string $field Vrednost podatka
     * @param mixed $option Vrednost parametra za zadovoljavanje pravila
     */
    protected function alnum($field, $value, $option)
    {
        return preg_match("/^[\p{L}\p{Z}A-Za-z0-9 ]+$/ui", $value);
    }

    /**
     * Pravilo - mora da odgovara drugom polju
     *
     * @param string $field Naziv podatka
     * @param string $field Vrednost podatka
     * @param mixed $option Vrednost parametra za zadovoljavanje pravila
     */
    protected function match_field($field, $value, $option)
    {
        return $value === $this->items[$option];
    }

    /**
     * Pravilo - mora da bude jedinstven u bazi
     *
     * @param string $field Naziv podatka
     * @param string $field Vrednost podatka
     * @param mixed $option Vrednost parametra za zadovoljavanje pravila
     */
    protected function unique($field, $value, $option)
    {
        $option = explode('.', $option);
        $sql = "SELECT COUNT(*) AS broj FROM {$option[0]} WHERE {$option[1]} = :{$option[1]}";
        $params= [":{$option[1]}" => $value];
        $res = $this->db->fetch($sql, $params);
        return (int)$res[0]->broj > 0 ? false : true;
    }

    /**
     * Pravilo - maksimum
     *
     * @param string $field Naziv podatka
     * @param string $field Vrednost podatka
     * @param mixed $option Vrednost parametra za zadovoljavanje pravila
     */
    protected function max($field, $value, $option)
    {
        return $value <= $option;
    }

    /**
     * Pravilo - minimum
     *
     * @param string $field Naziv podatka
     * @param string $field Vrednost podatka
     * @param mixed $option Vrednost parametra za zadovoljavanje pravila
     */
    protected function min($field, $value, $option)
    {
        return $value >= $option;
    }

    /**
     * Pravilo - jednakost
     *
     * @param string $field Naziv podatka
     * @param string $field Vrednost podatka
     * @param mixed $option Vrednost parametra za zadovoljavanje pravila
     */
    protected function equal($field, $value, $option)
    {
        // dd(gettype($option));
        return (string)$value === (string)$option;
    }

    /**
     * Dezinfekcija podataka
     *
     * @param array $data Niz sa podacima
     * @return array Niz sa dezinfikovanim podacima
     */
    public function sanitize(array $data)
    {
        return filter_var_array($data, FILTER_SANITIZE_STRING);
    }

    /**
     * Da li postoje greske validacije
     *
     * @return boolean
     */
    public function hasErrors()
    {
        return count($this->errors) > 0 ? true : false;
    }

    /**
     * Preuzimanje gresaka validacije
     *
     * Ako je prosledjen naziv podatka vraca sve greske za taj podatak
     * ili NULL ako nema gresaka. Ako se ne prosledi naziv podatka vraca
     * sve greske validacije.
     *
     * @param string $key Naziv podatka
     * @return array|null Niz gresaka ili NULL ako nema gresaka
     */
    public function getErrors(string $key = null)
    {
        if ($key) {
            return isset($this->errors[$key]) ? $this->errors[$key] : null;
        } else {
            return $this->hasErrors() ? $this->errors : null;
        }
    }

    /**
     * Vraca prvu gresku za podatak
     *
     * @param string $key Naziv podatka
     * @return string|null Prva greska za podatak ili NULL ako nema greske
     */
    public function getFirstError(string $key)
    {
        return isset($this->errors[$key][0]) ? $this->errors[$key][0] : null;
    }

}
