# Query Builder

Um query builder simples e fácil de usar, abstraindo a utilização do PDO de forma
produtiva e segura com filtragem dos dados.


### Recursos

- Instalação simples e sem dependências
- Abstração completa do CRUD e transações, e execução de queries com JOINs, ORDER, LIMIT, OFFSET, GROUP BY e HAVING.
- Métodos para INNER JOIN, LEFT JOIN E RIGHT JOIN com Subqueries
- Pode ser fácilmente extendida e personalizada

## Instalação

A instalação pode ser feita via composer:

```
composer require willry/querybuilder
```

## Documentação

Para mais detalhes sobre como usar, veja uma pasta de exemplo no diretório do componente. Nela terá um exemplo de uso para cada classe. Ele funciona assim:

#### Utilização:

```php
<?php

require_once __DIR__ . "/vendor/autoload.php";

//connection config file
require_once __DIR__ . "/config.php";

use Willry\QueryBuilder\Connect;
use Willry\QueryBuilder\DB;
use Willry\QueryBuilder\Query;

/**
 * Leitura de dados basicos(multiplos resultados)
 */
$dados = DB::table("users as u")
    ->select(["u.id", "u.first_name", "u.email"]) // OR ->selectRaw("u.id, u.first_name, u.email")
    ->where("id <= :num", ["num" => 5])
    ->where("email is not null")
    ->order("id ASC")
    ->get();
var_dump($dados);


/**
 * Leitura de dados basicos(único resultado)
 */
$dados = DB::table("users as u")
    ->select(["u.id", "u.first_name", "u.email"]) // OR ->selectRaw("u.id, u.first_name, u.email")
    ->where("id <= :num", ["num" => 5])
    ->where("email is not null")
    ->order("id ASC")
    ->first();
var_dump($dados);


/**
 * Leitura de dados com parametros via metodo: ->params()
 */
$dados = DB::table("users as u")
    ->select(["u.id", "u.first_name", "u.email"]) // OR ->selectRaw("u.id, u.first_name, u.email")
    ->where("id <= :num")
    ->where("email is not null")
    ->order("id ASC")
    ->params([
        "num" => 5
    ])
    ->get();
var_dump($dados);


/**
 * JOINS
 * join()
 * leftJoin()
 * rightJoin()
 */
$join = DB::table("users as u")
    ->selectRaw("u.id, u.first_name, u.email, ad.name as address")
    ->leftJoin("address AS ad ON ad.user_id = u.id AND ad.name LIKE :name", ["name" => '%a%'])
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
$address = DB::table("address")->where("name is not null");

$users = DB::table("users as u")
    ->selectRaw("u.id as usuario, sub.name as rua")
    ->leftJoinSub($address, 'sub', "sub.user_id = u.id")
    ->get();
var_dump($users);

/**
 * Selecionar de uma subquery
 *
 * Select * from (select * from users) as sub
 */
$dados = DB::fromSub(function (Query $query) {
    return $query->from("users")->limit(10);
}, 'sub')->get();
var_dump($dados);

/**
 * Condições dinamicas(where de acordo com a necessidade)
 */
$dinamico = DB::table("users as u")->select(["u.id", "u.first_name", "u.email"]);

$filtroId = 5;

if (!empty($filtroId)) {
    $dinamico->where("u.id <= :num", ['num' => $filtroId]);
}
var_dump($dinamico->get());


/**
 * WHERE IN
 */
$dinamico = DB::table("users as u")
    ->selectRaw("u.id, u.first_name, u.email")
    ->whereIn("u.id", [1, 2, 3, 4, 5])
    ->get();
var_dump($dinamico);

/**
 * UPDATE
 */
$update = DB::table("users as u")
    ->where("id = :num", ['num' => 53])
    ->update([
        "email" => "fulano@fulano.com"
    ]);
var_dump($update);

/**
 * DELETE
 */
$delete = DB::table("users as u")
    ->where("id > :num", ['num' => 56])
    ->delete();
var_dump($delete);


/**
 * Create
 */
$create = DB::table("users")
    ->create([
        "email" => "fulano" . time() . "@fulano.com"
    ]);
var_dump($create);


/**
 * Query sem query builder
 */
$stmt = Connect::getInstance()->prepare('
            select
                u.id,
                u.first_name,
                GROUP_CONCAT(distinct CONCAT_WS(";", p.id, p.`number`) SEPARATOR "|") as numeros,
                GROUP_CONCAT(distinct CONCAT_WS(";", a.id, a.name) SEPARATOR "|") as enderecos
            from
                users u
            left join phone p on
                p.user_id = u.id
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
 * DEBUG QUERY
 */

$sql = DB::table("users as u")
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
    ->toSQL();
var_dump($sql);

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


/**
 * Create Dynamic SQL filters,
 * generating query string and array with bind params
 */

$urlFilter = $_GET['filter'] ?? null;

$filtersArrReference = [];

Query::dynamicQueryFilters($filtersArrReference, 'ID = :ID', ['ID' => 1]);

if ($urlFilter) {
    Query::dynamicQueryFilters($filtersArrReference, 'FILTER = :FILTER', ['FILTER' => "%$urlFilter%"]);
}


$sql = DB::table("users as u")
    ->select([
        "u.id",
        "u.name"
    ])
    ->where($filtersArrReference['queryString'])
    ->params($filtersArrReference['binds'])
    ->toSQL();

var_dump($sql);

```

### Configuração

Por padrão, é recomendado a utilização da configuração

```php
define("CONF_PDO_OPT",[
    \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION, // pdo lançar exceptions
    \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_OBJ, // faz resultados virem como classes stdClass
    \PDO::ATTR_CASE => \PDO::CASE_NATURAL
]);
```

## Contribuição

Veja [CONTRIBUTING](https://github.com/willry/querybuilder/blob/master/CONTRIBUTING.md) para mais detalhes.

## Suporte

Se você descobrir algum problema relacionado à segurança, entre em contato.


## Licença

Licença: MIT. Veja [License File](https://github.com/willry/querybuilder/blob/master/LICENSE) para mais informações.