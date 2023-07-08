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
     * @var array
     */
    protected $columns = [];

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
     * @var \PDO|null
     */
    protected $db;

    /**
     * @var array|null
     */
    protected $connectionConfig;

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

    public function setSilentErrors(bool $silentErrors = false)
    {
        $this->silentErrors = $silentErrors;
    }

    public function fromSubQuery(Query $query, string $alias = 'sub')
    {
        $this->entity = "(" . $query->toSQL() . ") as $alias";
        return $this;
    }


    /**
     * @param string $columns
     * @return $this
     */
    public function selectRaw(string $columns = "*", array $params = []): Query
    {
        $this->columns = array_merge($this->columns, explode(',', $columns));
        $this->params($params);
        return $this;
    }

    public function select(array $columns = [], array $params = []): Query
    {
        $columns = empty($columns) ? ['*'] : $columns;

        $this->columns = array_merge($this->columns, $columns);
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
        $inPlaceHolder = [];
        foreach ($data as $d) {
            $paramNum = count($this->params) + 1;
            $paramName = "in{$paramNum}";
            $inPlaceHolder[] = ":{$paramName}";
            $this->params([
                $paramName => $d
            ]);
        }

        $inString = implode(",", $inPlaceHolder);

        if (!empty($this->where)) {
            $this->where .= " AND {$column} IN ({$inString})";
            return $this;
        }

        $this->where = "WHERE {$column} IN ({$inString})";
        return $this;
    }

    public function orWhereIn(string $column, array $data): Query
    {
        $columnUppercase = ucfirst($column);

        $inPlaceHolder = [];
        foreach ($data as $d) {
            $paramNum = count($this->params) + 1;
            $paramName = "in{$columnUppercase}{$paramNum}";
            $inPlaceHolder[] = ":{$paramName}";
            $this->params([
                $paramName => $d
            ]);
        }

        $inString = implode(",", $inPlaceHolder);

        if (!empty($this->where)) {
            $this->where .= " OR {$column} IN ($inString)";
            return $this;
        }

        $this->where = "WHERE {$column} IN ($inString)";
        return $this;
    }


    /**
     * @param string $columnOrder
     */
    public function order(string $columnOrder): Query
    {
        $this->order = "ORDER BY :order";
        $this->params(['order' => $columnOrder]);
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
        $groupBind = implode(',', array_fill(0, count($group), '?'));
        foreach ($group as $key => $g) {
            $this->params(["group_{$key}" => $g]);
        }
        $this->groupBy = "GROUP BY $groupBind";
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
            $stmt = $this->db->prepare($this->query);
            QueryHelpers::bind($stmt, $this->prepareParams());
            $stmt->execute();

            if (!$stmt->rowCount()) {
                return [];
            }

            return $stmt->fetchAll(\PDO::FETCH_CLASS);
        } catch (\PDOException $exception) {
            $this->handleError($exception);
        }
    }

    public function first(): ?stdClass
    {
        $this->mountQuery();

        try {
            $stmt = $this->db->prepare($this->query);
            QueryHelpers::bind($stmt, $this->prepareParams());
            $stmt->execute();

            if (!$stmt->rowCount()) {
                return null;
            }

            return $stmt->fetchObject();
        } catch (\PDOException $exception) {
            $this->handleError($exception);
        }
    }

    public function count(): ?int
    {
        $this->mountQuery();

        try {
            $stmt = $this->db->prepare($this->query);
            QueryHelpers::bind($stmt, $this->prepareParams());
            $stmt->execute();

            return $stmt->rowCount();
        } catch (\PDOException $exception) {
            $this->handleError($exception);
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

            $stmt = $this->db->prepare("INSERT INTO {$this->entity} ({$columns}) VALUES ({$values})");
            $this->params($data);

            QueryHelpers::bind($stmt, $this->prepareParams());

            $stmt->execute();

            return $this->db->lastInsertId();
        } catch (\PDOException $exception) {
            $this->handleError($exception);
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

            $stmt = $this->db->prepare("UPDATE {$this->entity} SET {$dateSet} {$this->where}");

            $this->params($data);

            QueryHelpers::bind($stmt, $this->prepareParams());

            $stmt->execute();

            return $stmt->rowCount() ?? 1;
        } catch (\PDOException $exception) {
            $this->handleError($exception);
        }
    }

    public function delete(): ?int
    {
        try {
            $stmt = $this->db->prepare("DELETE FROM {$this->entity} {$this->where}");
            QueryHelpers::bind($stmt, $this->prepareParams());
            $stmt->execute();
            return $stmt->rowCount() ?? 1;
        } catch (\PDOException $exception) {
            $this->handleError($exception);
        }
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
    private function handleError(\Exception $exception)
    {
        if (!$this->silentErrors) {
            throw $exception;
        }

        $this->fail = $exception;
    }


    private function mountQuery(): void
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
            "raw_params" => $this->params(),
            "filtered_params" => QueryHelpers::applyDefaultFilter($this->prepareParams())
        ];
    }

    public function toSQL()
    {
        $this->mountQuery();
        return $this->query;
    }


    private function prepareParams()
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

}
