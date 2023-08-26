<?php

namespace Willry\QueryBuilder;


class Base
{
    /**
     * @var bool
     */
    protected $silentErrors;

    /**
     * @var \Exception|null
     */
    protected $fail;

    /**
     * @var string $entity database table
     */
    protected $entity;

    /**
     * @var array
     */
    protected $columns = [];

    /**
     * @var string
     */
    protected $where;

    /**
     * @var string
     */
    protected $order;

    /**
     * @var string
     */
    protected $limit;

    /**
     * @var string
     */
    protected $offset;

    /**
     * @var string
     */
    protected $joins;

    /**
     * @var string
     */
    protected $query;

    /**
     * @var string
     */
    protected $groupBy;

    /**
     * @var string
     */
    protected $having;

    /**
     * @var \PDO
     */
    protected $db;

    protected $fields = [];

    public $bindings = [
        'select' => [],
        'from' => [],
        'join' => [],
        'create' => [],
        'update' => [],
        'where' => [],
        'groupBy' => [],
        'having' => [],
        'order' => [],
        'union' => [],
        'unionOrder' => [],
    ];

    public $type = "select";

    /**
     * @var array|null
     */
    protected $connectionConfig;

    public const TYPE_SELECT = 'select';

    public const TYPE_CREATE = 'create';

    public const TYPE_UPDATE = 'update';

    public const TYPE_DELETE = 'delete';


    public function __construct(string $connectionName = 'default', bool $regenerateConnection = false)
    {
        $this->db = Connect::getInstance($connectionName, $regenerateConnection);
        $this->connectionConfig = Connect::getConfig($connectionName);
    }

    public function from(string $entity)
    {
        $this->entity = $entity;
        return $this;
    }


    public function fromSubQuery($callback, string $alias = 'sub')
    {
        $query = new Query();
        $callback($query);

        $this->entity = "(" . $query->toSQL() . ") as $alias";
        $this->setBindings($query->flatBindings(), 'from');

        return $this;
    }

    public function setSilentErrors(bool $silentErrors = false)
    {
        $this->silentErrors = $silentErrors;
    }


    /**
     * @param string $where
     * @return $this
     */
    public function where(string $where, array $params = []): static
    {
        $this->setBindings($params, 'where');

        if (!empty($this->where)) {
            $this->where .= " AND {$where}";
            return $this;
        }

        $this->where = "WHERE $where";
        return $this;
    }

    /**
     * @param string $where
     * @return $this
     */
    public function orWhere(string $where, array $params = []): static
    {
        $this->setBindings($params, 'where');
        if (!empty($this->where)) {
            $this->where .= " OR {$where}";
            return $this;
        }

        $this->where = "WHERE $where";
        return $this;
    }

    public function whereIn(string $column, array $data): static
    {
        $inString = implode(',', array_fill(0, count($data), '?'));
        $this->setBindings($data, 'where');

        if (!empty($this->where)) {
            $this->where .= " AND {$column} IN ({$inString})";
            return $this;
        }

        $this->where = "WHERE {$column} IN ({$inString})";
        return $this;
    }

    public function orWhereIn(string $column, array $data): static
    {

        $inString = implode(',', array_fill(0, count($data), '?'));
        $this->setBindings($data, 'where');

        if (!empty($this->where)) {
            $this->where .= " OR {$column} IN ($inString)";
            return $this;
        }

        $this->where = "WHERE {$column} IN ($inString)";
        return $this;
    }


    /**
     * @param int $limit
     */
    public function limit(int $limit): static
    {
        $this->limit = "LIMIT $limit";
        return $this;
    }

    /**
     * @param int $offset
     * @return DB
     */
    public function offset(int $offset): static
    {
        $this->offset = "OFFSET $offset";
        return $this;
    }


    public function getBindings()
    {
        return $this->bindings;
    }

    /**
     * @param array $params
     * @return $this
     */
    public function setBindings(array $params = [], string $type = 'where'): static
    {
        $this->bindings[$type] = array_merge($this->bindings[$type], $params);

        return $this;
    }

    /**
     * @param string $join
     * @return $this
     */
    public function leftJoin(string $join, array $params = []): static
    {
        $this->setBindings($params, 'join');

        $this->joins .= PHP_EOL;

        $this->joins .= "LEFT JOIN $join";

        return $this;
    }

    /**
     * @param string $join
     * @return $this
     */
    public function rightJoin(string $join, array $params = []): static
    {
        $this->setBindings($params, 'join');

        $this->joins .= PHP_EOL;

        $this->joins .= "RIGHT JOIN $join";

        return $this;
    }

    /**
     * @param string $join
     * @return $this
     */
    public function join(string $join, array $params = []): static
    {
        $this->setBindings($params, 'join');

        $this->joins .= PHP_EOL;

        $this->joins .= "INNER JOIN $join";
        return $this;
    }

    public function joinSub(Query $subquery, string $alias, $condition, array $params = [])
    {
        $this->setBindings($subquery->flatBindings(), 'join');
        $this->setBindings($params, 'join');

        $this->joins .= PHP_EOL;

        $this->joins .= "INNER JOIN ({$subquery->toSQL()}) AS $alias ON $condition";

        return $this;
    }

    public function leftJoinSub(Query $subquery, string $alias, $condition, array $params = [])
    {
        $this->setBindings($subquery->flatBindings(), 'join');
        $this->setBindings($params, 'join');

        $this->joins .= PHP_EOL;

        $this->joins .= "LEFT JOIN ({$subquery->toSQL()}) AS $alias ON $condition";

        return $this;
    }

    public function rightJoinSub(Query $subquery, string $alias, $condition, array $params = [])
    {
        $this->setBindings($subquery->flatBindings(), 'join');
        $this->setBindings($params, 'join');

        $this->joins .= PHP_EOL;

        $this->joins .= "RIGHT JOIN ({$subquery->toSQL()}) AS $alias ON $condition";

        return $this;
    }





    public function beginTransaction(): bool
    {
        return $this->db->beginTransaction();
    }


    public function commit(): bool
    {
        return $this->db->commit();
    }


    public function rollback(): bool
    {
        return $this->db->rollBack();
    }

    /**
     * @param  $exception
     * @throws Exception
     */
    protected function handleError(\Exception $exception)
    {
        if (!$this->silentErrors) {
            throw $exception;
        }

        $this->fail = $exception;
    }


    protected function mountQuery(): void
    {

        $columns = !empty($this->columns) ? $this->columns : ['*'];
        $columns = implode(',', $columns);
        $this->query = "SELECT $columns FROM $this->entity $this->joins $this->where $this->groupBy $this->having $this->order $this->limit $this->offset";
    }

   



    public function dump(): array
    {
        $this->mountQuery();

        return [
            "query" => $this->query,
            "raw_params" => $this->bindings,
        ];
    }

    public function toSQL()
    {
        $this->mountQuery();
        return $this->query;
    }


    public function flatBindings()
    {
        $params = [];
        foreach ($this->bindings as $key => $binds) {
            $params = array_merge($params, $this->bindings[$key]);
        }

        return $params;
    }

    public function mergeBindFromAnotherQuery(Query $query)
    {
        $array1 = $this->getBindings();
        $array2 = $query->getBindings();
        $mergedArray = [
            'select' => array_merge($array1['select'], $array2['select']),
            'from' => array_merge($array1['from'], $array2['from']),
            'join' => array_merge($array1['join'], $array2['join']),
            'update' => array_merge($array1['update'], $array2['update']),
            'where' => array_merge($array1['where'], $array2['where']),
            'groupBy' => array_merge($array1['groupBy'], $array2['groupBy']),
            'having' => array_merge($array1['having'], $array2['having']),
            'order' => array_merge($array1['order'], $array2['order']),
            'union' => array_merge($array1['union'], $array2['union']),
            'unionOrder' => array_merge($array1['unionOrder'], $array2['unionOrder']),
        ];

        $this->bindings = $mergedArray;
    }
}