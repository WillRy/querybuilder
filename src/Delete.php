<?php

namespace Willry\QueryBuilder;

use Exception;
use PDOStatement;
use stdClass;

class Delete extends Base
{


    protected function mountQuery(): void
    {
        $this->query = "DELETE FROM {$this->entity} {$this->where}";
    }

    public function delete(): ?int
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
