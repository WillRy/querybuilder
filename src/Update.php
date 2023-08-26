<?php

namespace Willry\QueryBuilder;

use Exception;
use PDOStatement;
use stdClass;

class Update extends Base
{
    protected function mountQuery(): void
    {

        $dateSet = [];
        foreach ($this->fields as $bind => $value) {
            $dateSet[] = "{$bind} = ?";
        }
        $dateSet = implode(", ", $dateSet);

        $this->query = "UPDATE {$this->entity} {$this->joins} SET {$dateSet} {$this->where}";
    }

    /**
     * @param array $data
     */
    public function update(array $data): static
    {
        $this->type = self::TYPE_UPDATE;

        $this->setBindings(array_values($data), 'update');
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

            return $stmt->rowCount() ?? 1;
        } catch (\PDOException $exception) {
            $this->handleError($exception);
        }
    }
}
