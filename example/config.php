<?php

define("CONF_PDO_OPT", [
    \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
    \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_OBJ,
    \PDO::ATTR_CASE => \PDO::CASE_NATURAL
]);

define("CONF_DB_HOST", "127.0.0.1");
define("CONF_DB_DRIVER", "mysql");
define("CONF_DB_PORT", "3306");
define("CONF_DB_USER", "root");
define("CONF_DB_PASS", "root");
define("CONF_DB_NAME", "fullstackphp");
