<?php


namespace Willry\QueryBuilder;


class QueryHelpers
{


    public static function bind(\PDOStatement &$stmt, array $params = [])
    {
//        var_dump($params);die;
//
        foreach ($params as $bind) {
            $stmt->bindValue(":{$bind['name']}", $bind['value'], $bind['filter']);
        }

        return $params;
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

    public static function random_string(string $type = 'alnum', int $len = 8): string
    {
        switch ($type) {
            case 'alnum':
            case 'numeric':
            case 'nozero':
            case 'alpha':
                switch ($type) {
                    case 'alpha':
                        $pool = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
                        break;

                    case 'alnum':
                        $pool = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
                        break;

                    case 'numeric':
                        $pool = '0123456789';
                        break;

                    case 'nozero':
                        $pool = '123456789';
                        break;
                }

                return substr(str_shuffle(str_repeat($pool, (int)ceil($len / strlen($pool)))), 0, $len);

            case 'md5':
                return md5(uniqid((string)mt_rand(), true));

            case 'sha1':
                return sha1(uniqid((string)mt_rand(), true));

            case 'crypto':
                return bin2hex(random_bytes($len / 2));
        }
        // 'basic' type treated as default
        return (string)mt_rand();
    }

}