<?php

namespace Willry\QueryBuilder;

/**
 * Class Connect Singleton Pattern
 */
class Connect
{
    /**
     * @const array
     */
    private const OPTIONS = CONF_PDO_OPT;

    /**
     * @var \PDO
     */
    private static $instance;

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

    public static function getInstance(): ?\PDO
    {
        if (empty(self::$instance)) {
            try {
                self::$instance = new \PDO(
                    CONF_DB_DRIVER . ':host=' . CONF_DB_HOST . ';port=' . CONF_DB_PORT . ';dbname=' . CONF_DB_NAME,
                    CONF_DB_USER,
                    CONF_DB_PASS,
                    self::OPTIONS
                );
            } catch (\PDOException $exception) {
                die('Connection error');
            }
        }

        return self::$instance;
    }
}
