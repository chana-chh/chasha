<?php

namespace App\Classes;

use \PDO;

class Config
{
    public static $container;

    public static $config = [
        'pagination' => [
            'per_page' => 10,
            'page_span' => 3,
            'css_class' => 'pgn-btn',
            'css_current_class' => 'pgn-cur-btn',
        ],
    ];
}
