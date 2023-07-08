<?php

namespace Willry\QueryBuilder;

/**
 * Class Connect Singleton Pattern
 */
class Connect
{

    /** @var array */
    private static $instance;

    /** @var \Exception|null */
    private static $error = null;

    private static $configurations = [];

    /**
     * Connect constructor. Private singleton
     */
    private function __construct()
    {
    }

    /**
     * Connect clone. Private singleton
     */
    private function __clone()
    {
    }

    public static function getInstance($dbName = null, $regenerateConnection = false): ?\PDO
    {
        $configurations = self::$configurations ?? [];
        $default = reset($configurations);

        $dbConf = $configurations[$dbName] ?? $default;

        $dbDsn = $dbConf["driver"] . ":host=" . $dbConf["host"] . ";dbname=" . $dbConf["dbname"] . ";port=" . $dbConf["port"];

        //DSN alternative for SQL Server (sqlsrv)
        if ($dbConf['driver'] == 'sqlsrv') {
            $dbDsn = $dbConf["driver"] . ":Server=" . $dbConf["host"] . "," . $dbConf["port"] . ";Database=" . $dbConf["dbname"];
        }

        if (empty(self::$instance[$dbName]) || $regenerateConnection) {
            try {
                self::$instance[$dbName] = new \PDO(
                    $dbDsn,
                    $dbConf["username"],
                    $dbConf["passwd"],
                    $dbConf["options"]
                );
            } catch (\PDOException $exception) {
                self::$error = $exception;
            }
        }

        return self::$instance[$dbName];
    }

    /**
     * @return \Exception|null
     */
    public static function getError(): ?\Exception
    {
        return self::$error;
    }

    public static function config(array $configurations)
    {
        self::$configurations = $configurations;
    }

    public static function getConfig(string $connectionName): ?array
    {
        return self::$configurations[$connectionName] ?? null;
    }
}
