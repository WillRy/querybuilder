<?php

namespace Willry\QueryBuilder;

use Exception;
use PDOStatement;
use stdClass;

abstract class Query
{

    /**
     * @var bool
     */
    protected $silentErrors;

    /**
     * @var \PDOException|null
     */
    protected $fail;

    /**
     * @var string $entity database table
     */
    protected $entity;

    /**
     * @var string
     */
    protected $columns = "*";

    /**
     * @var string
     */
    protected $where;

    /**
     * @var array
     */
    protected $params = [];

    /**
     * @var string
     */
    protected $order;

    /**
     * @var int
     */
    protected $limit;

    /**
     * @var int
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
     * @param string $entity
     * @param bool $silentErrors
     */
    public function __construct(string $entity, bool $silentErrors = false)
    {
        $this->entity = $entity;
        $this->silentErrors = $silentErrors;
    }

    /**
     * @param string $columns
     * @return $this
     */
    public function selectRaw(string $columns = "*", array $params = []): Query
    {
        $this->columns = $columns;
        $this->params($params);
        return $this;
    }

    public function select(array $columns = ["*"], array $params = []): Query
    {
        $this->columns = implode(",", $columns);
        $this->params($params);
        return $this;
    }

    /**
     * @param string $where
     * @return $this
     */
    public function where(string $where, array $params = []): Query
    {
        $this->params($params);

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
    public function orWhere(string $where, array $params = []): Query
    {
        $this->params($params);

        if (!empty($this->where)) {
            $this->where .= " OR {$where}";
            return $this;
        }

        $this->where = "WHERE $where";
        return $this;
    }

    public function whereIn(string $column, array $data): Query
    {
        $inQuery = implode(',', array_fill(0, count($data), '?'));

        foreach ($data as $key => $d) {
            $this->params([
                $key => $d
            ]);
        }

        if (!empty($this->where)) {
            $this->where .= " AND {$column} IN ($inQuery)";
            return $this;
        }

        $this->where = "WHERE {$column} IN ($inQuery)";
        return $this;
    }

    public function orWhereIn(string $column, array $data): Query
    {
        $inQuery = implode(',', array_fill(0, count($data), '?'));

        foreach ($data as $key => $d) {
            $this->params([
                $key => $d
            ]);
        }

        if (!empty($this->where)) {
            $this->where .= " OR {$column} IN ($inQuery)";
            return $this;
        }

        $this->where = "WHERE {$column} IN ($inQuery)";
        return $this;
    }


    /**
     * @param string $columnOrder
     */
    public function order(string $columnOrder): Query
    {
        $this->order = "ORDER BY {$columnOrder}";
        return $this;
    }

    /**
     * @param int $limit
     */
    public function limit(int $limit): Query
    {
        $this->limit = "LIMIT :limit";
        $this->params(['limit' => $limit]);
        return $this;
    }

    /**
     * @param int $offset
     * @return DB
     */
    public function offset(int $offset): Query
    {
        $this->offset = "OFFSET :offset";
        $this->params(['offset' => $offset]);
        return $this;
    }

    /**
     * @param array $group
     * @return DB
     */
    public function groupBy(array $group): Query
    {
        $this->groupBy = "GROUP BY " . implode(", ", $group);
        return $this;
    }

    /**
     * @param string $having
     * @return DB
     */
    public function having(string $having): Query
    {
        $this->having = "HAVING $having";
        return $this;
    }

    /**
     * @param array $params
     * @return $this
     */
    public function params(array $params = []): Query
    {
        $this->params = array_merge($this->params, $params);

        return $this;
    }

    /**
     * @param string $join
     * @return $this
     */
    public function leftJoin(string $join, array $params = []): Query
    {
        $this->params($params);

        if (!empty($this->joins)) {
            $this->joins .= PHP_EOL;
        }
        $this->joins .= "LEFT JOIN $join";
        return $this;
    }

    /**
     * @param string $join
     * @return $this
     */
    public function rightJoin(string $join, array $params = []): Query
    {
        $this->params($params);

        if (!empty($this->joins)) {
            $this->joins .= PHP_EOL;
        }
        $this->joins .= "RIGHT JOIN $join";

        return $this;
    }

    /**
     * @param string $join
     * @return $this
     */
    public function join(string $join, array $params = []): Query
    {
        $this->params($params);

        if (!empty($this->joins)) {
            $this->joins .= PHP_EOL;
        }
        $this->joins .= "INNER JOIN $join";
        return $this;
    }

    public function joinSub(Query $subquery, string $alias, $condition)
    {
        if (!empty($this->joins)) {
            $this->joins .= PHP_EOL;
        }
        $this->joins .= "INNER JOIN ({$subquery->toSQL()}) AS $alias ON $condition";
        return $this;
    }

    public function leftJoinSub(Query $subquery, string $alias, $condition)
    {
        if (!empty($this->joins)) {
            $this->joins .= PHP_EOL;
        }
        $this->joins .= "LEFT JOIN ({$subquery->toSQL()}) AS $alias ON $condition";
        return $this;
    }

    public function rightJoinSub(Query $subquery, string $alias, $condition)
    {
        if (!empty($this->joins)) {
            $this->joins .= PHP_EOL;
        }
        $this->joins .= "RIGHT JOIN ({$subquery->toSQL()}) AS $alias ON $condition";
        return $this;
    }

    public function get(): ?array
    {
        $this->mountQuery();

        try {
            $stmt = Connect::getInstance()->prepare($this->query);
            $this->bind($stmt);
            $stmt->execute();

            if (!$stmt->rowCount()) {
                return [];
            }

            return $stmt->fetchAll(\PDO::FETCH_CLASS);
        } catch (\PDOException $exception) {
            return $this->handleError($exception);
        }
    }

    public function first(): ?stdClass
    {
        $this->mountQuery();

        try {
            $stmt = Connect::getInstance()->prepare($this->query);
            $this->bind($stmt);
            $stmt->execute();

            if (!$stmt->rowCount()) {
                return null;
            }

            return $stmt->fetchObject();
        } catch (\PDOException $exception) {
            return $this->handleError($exception);
        }
    }

    public function count(): ?int
    {
        $this->mountQuery();

        try {
            $stmt = Connect::getInstance()->prepare($this->query);
            $this->bind($stmt);
            $stmt->execute();

            return $stmt->rowCount();
        } catch (\PDOException $exception) {
            return $this->handleError($exception);
        }
    }


    /**
     * @param array $data
     * @return string|null
     */
    public function create(array $data)
    {
        try {
            $columns = implode(", ", array_keys($data));
            $values = ":" . implode(", :", array_keys($data));

            $stmt = Connect::getInstance()->prepare("INSERT INTO {$this->entity} ({$columns}) VALUES ({$values})");
            $this->bind($stmt);
            $stmt->execute();

            return Connect::getInstance()->lastInsertId();
        } catch (\PDOException $exception) {
            return $this->handleError($exception);
        }
    }

    /**
     * @param array $data
     */
    public function update(array $data): ?int
    {
        try {
            $dateSet = [];
            foreach ($data as $bind => $value) {
                $dateSet[] = "{$bind} = :{$bind}";
            }
            $dateSet = implode(", ", $dateSet);

            $stmt = Connect::getInstance()->prepare("UPDATE {$this->entity} SET {$dateSet} {$this->where}");

            $this->params($data);

            $this->bind($stmt);

            $stmt->execute();

            return $stmt->rowCount() ?? 1;
        } catch (\PDOException $exception) {
            return $this->handleError($exception);
        }
    }

    public function delete(): ?int
    {
        try {
            $stmt = Connect::getInstance()->prepare("DELETE FROM {$this->entity} {$this->where}");
            $this->bind($stmt);
            $stmt->execute();
            return $stmt->rowCount() ?? 1;
        } catch (\PDOException $exception) {
            return $this->handleError($exception);
        }
    }


    public static function beginTransaction(): bool
    {
        return Connect::getInstance()->beginTransaction();
    }


    public static function commit(): bool
    {
        return Connect::getInstance()->commit();
    }


    public static function rollback(): bool
    {
        return Connect::getInstance()->rollBack();
    }

    /**
     * @param  $exception
     * @return null
     * @throws Exception
     */
    private function handleError(\Exception $exception)
    {
        if ($this->silentErrors) {
            $this->fail = $exception;
            return null;
        }

        throw $exception;
    }


    private function mountQuery(): void
    {

        $this->query = "SELECT $this->columns FROM $this->entity $this->joins $this->where $this->groupBy $this->having $this->order $this->limit $this->offset";
    }

    /**
     * @param array $data
     * @return array|null
     */
    private function filter(array $data): ?array
    {
        $filter = [];
        foreach ($data as $key => $value) {
            $filter[$key] = (is_null($value) ? null : filter_var($value, FILTER_DEFAULT));
        }
        return $filter;
    }

    public function dump(): void
    {
        $this->mountQuery();

        var_dump(
            [
                "query" => $this->query,
                "raw_params" => $this->params(),
                "filtered_params" => $this->filter($this->finalParams())
            ]
        );
        exit;
    }

    public function toSQL()
    {
        $this->mountQuery();
        return $this->query;
    }

    public function finalParams()
    {
        $result = [];
        foreach ($this->params as $key => $param) {
            if (is_array($param)) {
                $result[$key] = implode(",", $param);
            } else {
                $result[$key] = $param;
            }
        }
        return $result;
    }

    public function bind(PDOStatement &$stmt)
    {
        $binds = $this->filter($this->finalParams());

        foreach($binds as $key => $bind) {
            if($key == 'limit' || $key == "offset") {
                $stmt->bindValue(":$key", $bind, \PDO::PARAM_INT);
            } else {
                $stmt->bindValue(":$key", $bind);
            }

        }

        return $bind;
    }
}
