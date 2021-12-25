<?php

namespace Willry\QueryBuilder;

abstract class Model
{
    public $table;
    public $db;

    public function __construct()
    {
        $this->table = $this->getTable();
        $this->db = DB::table($this->table);
    }

    protected function modelName()
    {
        return strtolower(substr(strrchr($this->pluralUSA(2, static::class), "\\"), 1));
    }

    public function getTable()
    {
        if (empty($this->table)) {
            return $this->table = $this->modelName();
        }
        return $this->table;
    }

    public function pluralUSA($quantity, $singular, $plural = null)
    {
        if ($quantity == 1 || !strlen($singular)) return $singular;
        if ($plural !== null) return $plural;

        $last_letter = strtolower($singular[strlen($singular) - 1]);
        switch ($last_letter) {
            case 'y':
                return substr($singular, 0, -1) . 'ies';
            case 's':
                return $singular . 'es';
            default:
                return $singular . 's';
        }
    }
}
