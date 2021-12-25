# Query Builder

Um query builder simples e fácil de usar, abstraindo a utilização do PDO de forma
produtiva e segura com filtragem dos dados.


### Recursos

- Instalação simples e sem dependências
- Abstração completa do CRUD e transações, e execução de queries com JOINs, ORDER, LIMIT, OFFSET, GROUP BY e HAVING.
- Métodos para INNER JOIN, LEFT JOIN E RIGHT JOIN com Subqueries
- Pode ser fácilmente extendida e personalizada

## Instalação

O query builder **EM BREVE** estará disponível via composer:

Por enquanto, pode ser baixado e usado normalmente, com sua própria personalização de autoload
nas classes DB, Connect e Query

## Documentação

Para mais detalhes sobre como usar, veja uma pasta de exemplo no diretório do componente. Nela terá um exemplo de uso para cada classe. Ele funciona assim:

#### Utilização:

```php
<?php

require_once __DIR__ . "/../vendor/autoload.php";

//connection config file
require_once __DIR__ . "/config.php";

use Willry\QueryBuilder\Connect;
use Willry\QueryBuilder\DB;
use Willry\QueryBuilder\Model;

/**
 * Leitura de dados basicos(multiplos resultados)
 */
$dados = DB::table("users as u")
    ->select(["u.id", "u.first_name", "u.email"]) // OR ->selectRaw("u.id, u.first_name, u.email")
    ->where("id >= :num")
    ->where("email is not null")
    ->params([
        "num" => 1
    ])
    ->order("id ASC")
    ->get();
var_dump($dados);

/**
 * Leitura de dados basicos(único resultado)
 */
$dados = DB::table("users as u")
    ->select(["u.id", "u.first_name", "u.email"]) // OR ->selectRaw("u.id, u.first_name, u.email")
    ->where("id >= :num")
    ->where("email is not null")
    ->params([
        "num" => 1
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
    ->selectRaw("u.id, u.first_name, u.email, ad.name as address")
    ->where("u.id >= :num")
    ->leftJoin("address AS ad ON ad.user_id = u.id")
    ->params([
        "num" => 1
    ])
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

if ($filtroId) {
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
 * Camada de models
 */
class User extends Model
{
    /**
     * opcional (identifica automatico pelo nome da model em ingles no plural)
     */
    //public $table = "users";


    /**
     * Lista com instancia pré configurada herdada da Model
     * @param int $limit
     * @param int $offset
     * @return array|null
     */
    public function list($limit = 10, $offset = 0)
    {
        return $this->db->select(["id", "first_name", "email", "status"])->limit($limit)->offset($offset)->get();
    }

    /**
     * Lista com Query builder diretamente
     * @param int $limit
     * @param int $offset
     * @return array|null
     */
    public function listAll()
    {
        return DB::table($this->table)->select(["id", "first_name", "email", "status"])->get();
    }
}


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
DB::table("users as u")
    ->select([
        "u.id",
        "count(ad.id) as qtd"
    ])
    ->join("address AS ad ON ad.user_id = u.id")
    ->groupBy(["u.id"])
    ->having("count(ad.id) > :qtd")
    ->params([
        "qtd" => 1
    ])
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


## Licença

Licença: MIT. Veja [License File](https://github.com/willry/querybuilder/blob/master/LICENSE) para mais informações.