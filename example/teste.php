<?php

require_once __DIR__ . "/../vendor/autoload.php";

//connection config file
require_once __DIR__ . "/config.php";

use Willry\QueryBuilder\Connect;
use Willry\QueryBuilder\DB;
use Willry\QueryBuilder\Query;

$dados = DB::table("users as u")
    ->where("id in (:ids)")
    ->params([
        'ids' => [1, 2, 3, 4, 5, 6, 7, 8, 9]
    ])
    ->first();

var_dump($dados);
