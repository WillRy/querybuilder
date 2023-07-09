<?php

use Willry\QueryBuilder\Connect;
use Willry\QueryBuilder\DB;

require_once __DIR__ . "/../vendor/autoload.php";


/**
 * Informar um array onde a chave é o nome da conexão
 * e dentro vai os dados da conexão para o PDO
 */
$connections = [
    'default' => [
        "driver" => "mysql",
        "host" => "127.0.0.1",
        "port" => "3306",
        "dbname" => "fullstackphp",
        "username" => "root",
        "passwd" => "root",
        "options" => [
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8",
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,
            PDO::ATTR_CASE => PDO::CASE_NATURAL
        ]
    ],
    'banco_teste' => [
        "driver" => "mysql",
        "host" => "127.0.0.1",
        "port" => "3306",
        "dbname" => "teste",
        "username" => "root",
        "passwd" => "root",
        "options" => [
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8",
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,
            PDO::ATTR_CASE => PDO::CASE_NATURAL
        ]
    ],
];

/**
 * @important
 *
 * Aqui injeta as configurações
 */
Connect::config($connections);


$dados = DB::table("users as u")
    ->select(["u.id", "u.first_name", "u.email"])
    ->limit(3)
    ->get();

var_dump($dados);

