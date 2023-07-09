<?php


namespace Willry\QueryBuilder;


class QueryHelpers
{


    public static function bind(\PDOStatement &$stmt, array $params = [])
    {
        $defaultType = \PDO::PARAM_STR;

        foreach ($params as $key => $value) {
            $type = $defaultType;

            if (is_int($value)) {
                $type = \PDO::PARAM_INT;
            }

            if (is_resource($value)) {
                $type = \PDO::PARAM_LOB;
            }

            if (is_bool($value)) {
                $type = \PDO::PARAM_BOOL;
            }

            $stmt->bindValue(
                is_string($key) ? $key : $key + 1,
                $value,
                $type
            );
        }
    }

    public static function dynamicQueryFilters(array &$queryParams, string $queryString, array $bind)
    {
        $queryParams["filters"][] = $queryString;
        $queryParams["binds"] = !empty($queryParams["binds"]) ? array_merge($queryParams["binds"], $bind) : $bind;

        $queryString = "";

        if (empty($queryParams["filters"])) return "";

        foreach ($queryParams["filters"] as $key => $filtro) {
            if ($key === 0) {
                $queryString .= $filtro;
            } else {
                $queryString .= " AND {$filtro}";
            }
        }

        $queryParams["queryString"] = $queryString;

        return $queryParams;
    }

}