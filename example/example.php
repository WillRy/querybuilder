<?php

require_once __DIR__ . "/../vendor/autoload.php";

//connection config file
require_once __DIR__ . "/config.php";
require_once __DIR__ . "/helpers.php";

use Willry\QueryBuilder\Connect;
use Willry\QueryBuilder\Create;
use Willry\QueryBuilder\Delete;
use Willry\QueryBuilder\Query;
use Willry\QueryBuilder\QueryHelpers;
use Willry\QueryBuilder\Update;

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
/**
 * Leitura de dados basicos(trazer multiplos resultados)
 */
$data = (new Query())
    ->from('users as u')
    ->select(["u.id", "u.first_name", "u.email"]) // OR ->selectRaw("u.id, u.first_name, u.email")
    ->selectRaw("IF(1 = ?, 'verdadeiro','falso') AS boolean", [1])
    ->where("id >= ?", [1])
    ->where("email is not null")
    ->order("id ASC")
    ->get();


var_dump($data);


/**
 * Leitura de dados basicos(trazer único resultado)
 */
$data = (new Query())
    ->from('users as u')
    ->select(["u.id", "u.first_name", "u.email"]) // OR ->selectRaw("u.id, u.first_name, u.email")
    ->selectRaw("IF(1 = ?, 'verdadeiro','falso') AS boolean", [1])
    ->where("id <= ?", [5])
    ->where("email is not null")
    ->order("id ASC")
    ->first();
var_dump($data);


/**
 * Escolher a conexão de banco de dados
 */
$nomeConexao = 'default';
$data = (new Query($nomeConexao))
    ->from('users as u')
    ->select(["u.id", "u.first_name", "u.email"]) // OR ->selectRaw("u.id, u.first_name, u.email")
    ->selectRaw("IF(1 = ?, 'verdadeiro','falso') AS boolean", [1])
    ->where("id <= ?", [5])
    ->where("email is not null")
    ->order("id ASC")
    ->first();

var_dump($data);


/**
 * JOINS
 * join()
 * leftJoin()
 * rightJoin()
 */
$join = (new Query())
    ->from('users as u')
    ->selectRaw("u.id, u.first_name, u.email, ad.street as address")
    ->leftJoin("address as ad ON ad.user_id = u.id and ad.street LIKE ?", ['%a%'])
    ->limit(3)
    ->get();
var_dump($join);


/**
 * Join com subquery
 *
 * joinSub
 * leftJoinSub
 * rightJoinSub
 */

/** objeto de query builder, sem executar(->get(), ->first())*/
$address = (new Query())
    ->from('address as u')
    ->where("street is not null")
    ->where('id > ?', [1]);

$users = (new Query())
    ->from('users as u')
    ->selectRaw("u.id as usuario, sub.street as rua")
    ->leftJoinSub($address, 'sub', "sub.user_id = u.id AND 1 = ?", [1]);
var_dump($users->get());

/**
 * Selecionar de uma subquery
 *
 * Select * from (select * from users) as sub
 */
$dbSub = (new Query())->from('users as u')->where("2 = ?", [2])->limit(10);

$data = (new Query())->fromSubQuery(function (Query $query) {
    return $query->from("users")->selectRaw('id,first_name')->where("1 = ?", [1])->limit(10);
}, 'sub')
    ->where('4 = ?', [4])
    ->joinSub($dbSub, 'sub2', 'sub2.id = sub.id and 3 = ?', [3]);
var_dump($data->toSQL(), $data->flatBindings(), $data->get());


/**
 * WHERE IN
 */
$dinamico = (new Query())->from("users as u")
    ->selectRaw("u.id, u.first_name, u.email")
    ->whereIn("u.id", [1, 2, 3, 4, 5])
    ->get();
var_dump($dinamico);

/**
 * Create
 */
$create = (new Create())->from("users")
    ->create([
        'first_name' => 'fulano',
        'last_name' => 'qualquer' . generateRandomString(),
        "email" => "fulano" . generateRandomString() . "@fulano.com"
    ])->exec();
var_dump($create);

/**
 * UPDATE
 */
$update = (new Update())->from("users as u")
    ->where("id = ?", [$create])
    ->update([
        "email" => "fulano" . generateRandomString() . "@fulano.com"
    ])->exec();
var_dump($update);

/**
 * DELETE
 */
$delete = (new Delete())->from("users as u")
    ->where("id > ?", [56])
    ->delete();
var_dump($delete);


/**
 * Condições dinamicas(where de acordo com a necessidade)
 */
$dinamico = (new Query())->from("users as u")->select(["u.id", "u.first_name", "u.email"]);

$filtroId = 5;

if (!empty($filtroId)) {
    $dinamico->where("u.id <= ?", [$filtroId]);
}
var_dump($dinamico->get());

/**
 * Query sem query builder
 */
$stmt = Connect::getInstance()->prepare('
            select
                u.id,
                u.first_name,
                GROUP_CONCAT(distinct CONCAT_WS(";", a.id, a.street) SEPARATOR " | ") as enderecos
            from
                users u
            left join address a on
                a.user_id = u.id
            group by
                u.id
            limit 10 offset 0
        ');
$stmt->execute();
$result = $stmt->fetchAll(\PDO::FETCH_OBJ);
var_dump($result);

/**
 * Having
 */
$sql = (new Query())->from("users as u")
    ->select([
        "u.id",
        "count(ao.id) as qtd"
    ])
    ->join("app_orders as ao ON ao.user_id = u.id")
    ->groupBy("u.id")
    ->having("count(ao.id) > ?", [1])
    ->get();

var_dump($sql);
/**
 * DEBUG QUERY
 */

$sql = (new Query())->from("users as u")
    ->select([
        "u.id",
        "count(ao.id) as qtd"
    ])
    ->join("app_orders as ao ON ao.user_id = u.id")
    ->groupBy("u.id")
    ->having("count(ao.id) > ?", [1])
    ->toSQL();
var_dump($sql);


/**
 * CREATE
 */
$create = (new Create())->from("users")
    ->create([
        'first_name' => 'fulano',
        'last_name' => 'qualquer' . generateRandomString(),
        "email" => "fulano" . generateRandomString() . "@fulano.com"
    ])->dump();
var_dump($create);

/**
 * UPDATE
 */
$update = (new Update())->from("users as u")
    ->where("id = ?", [$create])
    ->update([
        "email" => "fulano" . generateRandomString() . "@fulano.com"
    ])->dump();
var_dump($update);

/**
 * DELETE
 */
$delete = (new Delete())->from("users as u")
    ->where("id > ?", [56])
    ->dump();
var_dump($delete);

/**
 * Criar filtros de queries de forma dinamica, gerando a string dos "where" e um array com bind params
 */

$urlFilter = filter_input(INPUT_GET, 'search', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

$filtersArrReference = [];

QueryHelpers::dynamicQueryFilters($filtersArrReference, 'id >= ?', [1]);

if ($urlFilter) {
    QueryHelpers::dynamicQueryFilters($filtersArrReference, 'first_name like ?', ["%$urlFilter%"]);
}


$sql = (new Query())->from("users as u")
    ->select(['*'])
    ->where($filtersArrReference['queryString'], $filtersArrReference['binds'])
    ->first();
var_dump($sql);
