<?php

namespace AppsLine\MySQLMapper;

use AppsLine\MySQLMapper\Utils;
use AppsLine\MySQLMapper\Query;

use AppsLine\MySQLMapper\Exception\ConnectionException;

class DB {

    private $connection;
    private $query;

    function __construct($connection = null) {
        if(!$connection instanceof Connection) {
            throw new ConnectionException('La conexión no es de tipo AppsLine\MySQLMapper\Connection');
        }

        $this -> connection = $connection;
    }

    public function execute($query, $params = null) {
        return $this -> connection -> execute($query, $params);
    }

    public function getError() {
        return $this -> connection -> getError();
    }

    public function setError() {
        $this -> connection -> rollBack();
    }

    public function lastInsertId() {
        return $this -> connection -> lastInsertId();
    }

    public function insert($options = null, $schema = null) {
        $schema = Utils :: isDefined($schema) ? $schema : debug_backtrace()[1]['object'];

        $this -> query = new Query($schema);
        $query = $this -> query -> insert($options);
        return $this -> execute($query['query'], $query['params']);
    }

    public function select($options = null, $schema = null) {
        $schema = Utils :: isDefined($schema) ? $schema : debug_backtrace()[1]['object'];

        $this -> query = new Query($schema);
        $query = $this -> query -> select($options);
        $result = $this -> execute($query['query'], $query['params']);
        if(is_array($result)) {
            $result = $this -> convertToSchema($schema, $result, $query['columns']['alias']);
        }
        return $result;
    }

    public function selectAll($options = null, $schema = null) {
        $schema = Utils :: isDefined($schema) ? $schema : debug_backtrace()[1]['object'];

        $this -> query = new Query($schema);
        $query = $this -> query -> selectAll($options);
        $result = $this -> execute($query['query'], $query['params']);
        if(is_array($result)) {
            $result = $this -> convertToSchema($schema, $result, $query['columns']['alias']);
        }
        return $result;
    }

    public function update($options = null, $schema = null) {
        $schema = Utils :: isDefined($schema) ? $schema : debug_backtrace()[1]['object'];

        $this -> query = new Query($schema);
        $query = $this -> query -> update($options);
        return $this -> execute($query['query'], $query['params']);
    }

    public function delete($options = null, $schema = null) {
        $schema = Utils :: isDefined($schema) ? $schema : debug_backtrace()[1]['object'];

        $this -> query = new Query($schema);
        $query = $this -> query -> delete($options);
        return $this -> execute($query['query'], $query['params']);
    }

    public function fetch($options = null, $schema = null) {
        $schema = Utils :: isDefined($schema) ? $schema : debug_backtrace()[1]['object'];
        
        $result = $this -> select($options, $schema);
        if(is_array($result)) {
            foreach ($this -> query -> columns as $attr => $column) {
                $value = $result[0] -> getValue($attr);
                $child_schema = $schema -> setValue($attr, $value);
                if($value !== null) {
                    if($schema -> attrIsRelation($attr)) {
                        if(method_exists($child_schema, 'fetch')) {
                            $child_schema -> fetch();
                        }
                    }
                }
            }
            return true;
        }
        return false;
    }

    public function convertToSchema($_schema, $_result, $_columns_alias) {
        $schemas = [];
        foreach ($_result as $key => $row) {
            $schema_ = new $_schema;
            foreach ($row as $alias => $value) {
                if($_columns_alias[$alias]['attr']) {
                    $attr = $_columns_alias[$alias]['attr'];
                    $schema_ -> setValue($attr, $value);
                } else {
                    $schema_ -> setValue($alias, $value);
                }
            }
            $schemas[] = $schema_;
        }
        return $schemas;
    }
}