<?php

namespace Willry\QueryBuilder;


class DB extends Query
{
    public static function table(
        string $entity,
        $connection = 'default',
        bool $silentErrors = false,
        bool $regenerateConnection = false
    ): DB
    {
        $db = new DB($connection, $regenerateConnection);
        $db->from($entity);
        $db->setSilentErrors($silentErrors);
        return $db;
    }

    public static function fromSub(
        callable $callback,
        string $alias,
        $connection = 'default',
        bool $silentErrors = false,
        bool $regenerateConnection = false
    ): DB
    {
        $db = new DB($connection, $regenerateConnection);
        $callback($db);
        $db->fromSubQuery($db, $alias);
        $db->setSilentErrors($silentErrors);
        return $db;
    }
}