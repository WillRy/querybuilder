# Query Builder

Um query builder simples e fácil de usar, abstraindo a utilização do PDO de forma
produtiva e segura com filtragem dos dados.


### Highlights

- Instalação simples e sem dependências
- Abstração completa do CRUD e transações, e execução de queries com JOINs, ORDER, LIMIT, OFFSET, GROUP BY e HAVING.
- Pode ser fácilmente extendida e personalizada

## Instalação

O query builder **EM BREVE** estará disponível via composer:

Por enquanto, pode ser baixado e usado normalmente, com sua própria personalização de autoload
nas classes DB, Connect e Query

## Documentation

Para mais detalhes sobre como usar, veja uma pasta de exemplo no diretório do componente. Nela terá um exemplo de uso para cada classe. Ele funciona assim:

#### Utilização:

```php
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
 * 
 * dump() -> show query string and params
 */
DB::table("users as u")
    ->select([
        "u.id",
        "count(ao.id) as qtd"
    ])
    ->join("app_orders as ao ON ao.user_id = u.id")
    ->groupBy(["u.id"])
    ->having("count(ao.id) > 1")
    ->dump();
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


## licença

The MIT License (MIT). Please see [License File](https://github.com/willry/querybuilder/blob/master/LICENSE) for more information.