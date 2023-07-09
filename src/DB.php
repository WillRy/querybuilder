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

        $dbSub = new DB($connection, $regenerateConnection);

        $callback($dbSub);
        $db->fromSubQuery($dbSub, $alias);

        $db->mergeBindFromAnotherQuery($dbSub);


        $db->setSilentErrors($silentErrors);
        return $db;
    }
}