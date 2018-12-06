<?php

namespace App\Classes;



class ClosureTest
{
    public function test(\Closure $closure)
    {
        $query = new QueryBuilder('predmet');
        return $closure($query);
    }

    public function ttt()
    {
        return 'ttt';
    }

    public function stat()
    {
        return new self;
    }
}