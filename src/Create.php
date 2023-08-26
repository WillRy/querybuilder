<?php

namespace Willry\QueryBuilder;

use Exception;
use PDOStatement;
use stdClass;

class Create extends Base
{
    protected function mountQuery(): void
    {
        $columns = implode(", ", array_keys($this->fields));
        $values = implode(',', array_fill(0, count($this->fields), '?'));

        $this->query = "INSERT INTO {$this->entity} ({$columns}) VALUES ({$values})";
    }

    /**
     * @param array $data
     * @return Create
     */
    public function create(array $data): static
    {
        $this->setBindings(array_values($data), 'create');
        
        $this->fields = $data;

        return $this;
    }

    /**
     * @param array $data
     */
    public function exec(): ?int
    {
        try {
            $this->mountQuery();

            $stmt = $this->db->prepare($this->query);

            QueryHelpers::bind($stmt, $this->flatBindings());

            $stmt->execute();

            return $this->db->lastInsertId();
        } catch (\PDOException $exception) {
            $this->handleError($exception);
        }
    }
}
