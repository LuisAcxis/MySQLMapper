<?php

namespace AppsLine\MySQLMapper;

use AppsLine\MySQLMapper\Utils;
use AppsLine\MySQLMapper\Config;
use AppsLine\MySQLMapper\DB;
use AppsLine\MySQLMapper\Connection;

use AppsLine\MySQLMapper\Exception\ConnectionException;

class Schema {

    private function getChildClass() {
        return get_class(debug_backtrace()[0]['object']);
    }
    
    private function getDBModel() {
        return Utils :: getConst($this -> getChildClass(), 'DB_MODEL');
    }

    protected function getDB() {
        $connection;

        if($this -> attrIsDefined('db_connection')) {
            $connection = $this -> getValue('db_connection');
        } else {
            $connection = Config :: getInstance() -> connection;
        }

        if(!$connection instanceof Connection) {
            throw new ConnectionException('La conexión no es de tipo AppsLine\MySQLMapper\Connection');
        }

        return new DB($connection);
    }

    public function getDBColumns() {
        $DB_MODEL = $this -> getDBModel();
        
        if(is_array($DB_MODEL)) {
            return $DB_MODEL['columns'];
        }
    }

    public function getDBTableName() {
        $DB_MODEL = $this -> getDBModel();
        
        if(is_array($DB_MODEL)) {
            return $DB_MODEL['table']['name'];
        }
    }

    public function getDBAttrs() {
        $dbColumns = $this -> getDBColumns();
        
        if(is_array($dbColumns)) {
            return array_keys($dbColumns);
        }
    }

    public function setValue($attr, $value) {
        $schema = $this;

        $schemas[0] = $schema;
        $arr = array_map('trim', explode('->', $attr));
        foreach ($arr as $key => $val) {
            if($key === end(array_keys($arr))) {
                $typeValue = $this -> getDBColumns()[$attr]['type'];
                switch ($typeValue) {
                    case 'i':
                    case 'int':
                    case 'integer':
                    case 'number':
                        $value = (int)$value;
                        break;
                    case 'float':
                    case 'f':
                    case 'double':
                    case 'd':
                    case 'decimal':
                        $value = (float)$value;
                        break;
                }

                $schemas[$key] -> {$arr[$key]} = $value;
            } else {
                $schemas[$key+1] = $schemas[$key] -> {$arr[$key]};
            }
        }
        return $schemas[count($schemas)-1];
    }

    public function getValue($attr) {
        $schema = $this;

        $arr = array_map('trim', explode('->', $attr));
        $value = clone $schema;
        foreach ($arr as $key => $val) {
            $value = $value -> {$val};
        }
        return $value;
    }

    public function attrIsDefined($attr) {
        $schema = $this;

        $arr = array_map('trim', explode('->', $attr));
        $vars[0] = get_object_vars($schema);
        foreach ($arr as $key => $value) {
            if(array_key_exists($value, $vars[$key])) {
                $vars[$key+1] = (array) $vars[$key][$value];
            }
            else {
                return false;
            }
        }
        return true;
    }

    public function attrIsRelation($attr) {
        $schema = $this;

        $schemas[0] = $schema;
        $pathFull = '';
        $cantRelations = 0;
        $arr = array_map('trim', explode('->', $attr));
        if(count($arr) > 1) {
            foreach ($arr as $key => $value) {
                if($key !== end(array_keys($arr))) {
                    $schemas[$key+1] = $schemas[$key] -> {$arr[$key]};
                    $pathFull .= $value . ' -> ';
                    $cantRelations++;
                    if(!is_object($schemas[$key+1])) {
                        return false;
                    }
                } else {
                    return [
                        schema => $schemas[$key],
                        attr => $value,
                        pathFull => $pathFull,
                        cantRelations => $cantRelations
                    ];
                }
            }
        }
        return false;
    }
}