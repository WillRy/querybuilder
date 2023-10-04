<?php

namespace Willry\QueryBuilder;

class Query extends Base
{

    /**
     * @param string $columns
     * @return $this
     */
    public function selectRaw(string $columns = "*", array $params = []): self
    {
        $this->setBindings($params, 'select');
        $this->columns = array_merge($this->columns, explode(',', $columns));
        return $this;
    }

    public function select(array $columns = ['*']): self
    {
        $this->columns = array_merge($this->columns, $columns);
        return $this;
    }

    /**
     * @param string $columnOrder
     */
    public function order(string $columnOrder): self
    {
        $this->order = "ORDER BY ?";
        $this->setBindings([$columnOrder], 'order');
        return $this;
    }

    /**
     * @param array $group
     * @return DB
     */
    public function groupBy(string $group): self
    {

        $this->groupBy = "GROUP BY {$group}";
        return $this;
    }

    /**
     * @param string $having
     * @return DB
     */
    public function having(string $having, array $params = []): self
    {
        $this->having = "HAVING {$having}";
        $this->setBindings($params, 'having');
        return $this;
    }

    /**
     * @param int $limit
     */
    public function limit(int $limit): static
    {
        $this->limit = "LIMIT ?";
        $this->setBindings([$limit], 'limit');
        return $this;
    }

    /**
     * @param int $offset
     * @return DB
     */
    public function offset(int $offset): static
    {
        $this->offset = "OFFSET ?";
        $this->setBindings([$offset], 'offset');
        return $this;
    }

    public function get(): ?array
    {
        $this->mountQuery();

        try {
            $stmt = $this->db->prepare($this->query);
            QueryHelpers::bind($stmt, $this->flatBindings());
            $stmt->execute();

            return $stmt->fetchAll(\PDO::FETCH_CLASS);
        } catch (\PDOException $exception) {
            $this->handleError($exception);
        }
    }

    public function first(): ?\stdClass
    {
        $this->mountQuery();

        try {
            $stmt = $this->db->prepare($this->query);
            QueryHelpers::bind($stmt, $this->flatBindings());
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
            QueryHelpers::bind($stmt, $this->flatBindings());
            $stmt->execute();

            return $stmt->rowCount();
        } catch (\PDOException $exception) {
            $this->handleError($exception);
        }
    }
}
