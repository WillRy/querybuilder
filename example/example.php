<?php

require_once __DIR__ . "/../vendor/autoload.php";

//connection config file
require_once __DIR__ . "/config.php";

use Willry\QueryBuilder\DB;

/**
 * Leitura de dados basicos(multiplos resultados)
 */
$dados = DB::table("users as u")
    ->select(["u.id", "u.first_name", "u.email"]) // OR ->selectRaw("u.id, u.first_name, u.email")
    ->where("id > :num")
    ->where("email is not null")
    ->params([
        "num" => 50
    ])
    ->order("id ASC")
    ->get();
var_dump($dados);

/**
 * Leitura de dados basicos(único resultado)
 */
$dados = DB::table("users as u")
    ->select(["u.id", "u.first_name", "u.email"]) // OR ->selectRaw("u.id, u.first_name, u.email")
    ->where("id > :num")
    ->where("email is not null")
    ->params([
        "num" => 50
    ])
    ->order("id ASC")
    ->first();
var_dump($dados);


/**
 * JOINS
 * join()
 * leftJoin()
 * rightJoin()
 */
$join = DB::table("users as u")
    ->selectRaw("u.id, u.first_name, u.email, sub.pay_status")
    ->where("u.id > :num")
    ->join("app_subscriptions as sub ON sub.user_id = u.id")
    ->join("app_subscriptions as sub2 ON sub2.user_id = u.id")
    ->params([
        "num" => 50
    ])
    ->get();
var_dump($join);



/**
 * Condições dinamicas(where de acordo com a necessidade)
 */
$dinamico = DB::table("users as u")
    ->selectRaw("u.id, u.first_name, u.email, sub.pay_status")
    ->join("app_subscriptions as sub ON sub.user_id = u.id")
    ->join("app_subscriptions as sub2 ON sub2.user_id = u.id")
    ->params([
        "num" => 50
    ]);

$filtroId = 50;

if($filtroId){
    $dinamico->where("u.id > :num");
}
var_dump($dinamico);


/**
 * UPDATE
 */
$update = DB::table("users as u")
    ->where("id = :num")
    ->params([
        "num" => 53
    ])
    ->update([
        "email" => "fulano@fulano.com"
    ]);
var_dump($update);

/**
 * DELETE
 */
$delete = DB::table("users as u")
    ->where("id > :num")
    ->params([
        "num" => 56
    ])
    ->delete();
var_dump($delete);



/**
 * Create
 */
$create = DB::table("users")
    ->create([
        "email" => "fulano" . time() . "@fulano.com"
    ]);


/**
 * DEBUG QUERY
 */
DB::table("users as u")
    ->select([
        "u.id",
        "count(ao.id) as qtd"
    ])
    ->join("app_orders as ao ON ao.user_id = u.id")
    ->groupBy(["u.id"])
    ->having("count(ao.id) > :qtd")
    ->params([
        "qtd" => 1
    ])
    ->dump();