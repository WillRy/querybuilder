<?php


namespace Willry\QueryBuilder;


class QueryHelpers
{

    public static function applyDefaultFilter(array $data): ?array
    {
        $filter = [];
        foreach ($data as $key => $value) {
            $filter[$key] = (is_null($value) ? null : filter_var($value, FILTER_DEFAULT));
        }
        return $filter;
    }

    public static function bind(\PDOStatement &$stmt, array $params = [])
    {
        $binds = self::applyDefaultFilter($params);

        foreach ($binds as $key => $bind) {
            if ($key == 'limit' || $key == "offset") {
                $stmt->bindValue(":$key", $bind, \PDO::PARAM_INT);
            } else {
                $stmt->bindValue(":$key", $bind);
            }
        }

        return $binds;
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