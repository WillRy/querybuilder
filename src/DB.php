<?php

namespace Willry\QueryBuilder;


class DB extends Query
{
    public static function table(string $entity, bool $silentErrors = false): DB
    {
        $db = new DB();
        $db->from($entity);
        $db->setSilentErrors($silentErrors);
        return $db;
    }

    public static function fromSub(callable $callback, string $alias): DB
    {
        $db = new DB();
        $callback($db);
        $db->fromSubQuery($db, $alias);
        return $db;
    }
}