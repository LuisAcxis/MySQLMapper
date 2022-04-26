<?php

namespace AppsLine\MySQLMapper;

use AppsLine\MySQLMapper\Utils;

use AppsLine\MySQLMapper\Exception\ConnectionException;

class Connection {

    private $connection;
    private $statement;
    private $result;

    private $query;

    private $onLoad;
    private $onUnload;

    function __construct($params) {
        if(!in_array("mysql", \PDO::getAvailableDrivers())) {
            throw new ConnectionException('No se encontrarón drivers para la conexión a PDO.');
        }

        $host = $params['host'];
        $user = $params['user'];
        $password = $params['password'];
        $database = $params['database'];
        $database = Utils :: isDefined($params['database']) ? $params['database'] : 'mysql';

        try {
            $this -> connection = new \PDO(
                'mysql:host=' . $host .
                ';dbname=' . $database,
                $user,
                $password,
                [\PDO :: ATTR_ERRMODE => \PDO :: ERRMODE_EXCEPTION]
            );
            if(!$this -> beginTransaction()) {
                throw new ConnectionException('Ocurrio un error al iniciar una nueva transacción en la base de datos.');
            }

            if($params['onLoad']) {
                $this -> onLoad = $params['onLoad'];
                $onLoad = $this -> onLoad;
                $onLoad($this);
            }

            if($params['onUnload']) {
                $this -> onUnload = $params['onUnload'];
            }

            return true;
        } catch(\PDOException $e) {
            $this -> connection = null;
            throw new ConnectionException($e -> getMessage());
        } catch (ConnectionException $e) {
            throw $e;
        }
    }

    function __destruct() {
        if($this -> onUnload) {
            $onUnload = $this -> onUnload;
            $onUnload($this);
        }

        $this -> commit($this -> connection);

        $this -> connection = null;
        $this -> statement = null;
        $this -> result = null;
        $this -> query = null;
    }

    public function execute($query, $params = null) {
        $this -> query = $query;

        if(!Utils :: isDefined($params)) {
            try {
                $this -> statement = $this -> connection -> query($this -> query);
            } catch(\PDOException $Exception) {
                throw new ConnectionException($Exception -> getMessage());
            }

            try {
                $this -> result = $this -> statement -> fetchAll(\PDO::FETCH_OBJ);
            } catch(\PDOException $e) {
                $this -> result = null;
            }
        }
        else {
            $this -> statement = $this -> connection -> prepare($this -> query);
            if(!$this -> statement) {
                throw new ConnectionException($this -> getError()[2]);
            }
            
            foreach ($params as $key => $value) {
                if($value[1] === null) {
                    $this -> statement -> bindParam($value[0], $value[1], \PDO::PARAM_INT);
                } else {
                    $this -> statement -> bindParam($value[0], $value[1], $this -> getTypeBindParam($value[2]));
                }
            }
            
            try {
                $this -> statement -> execute();
            } catch(\PDOException $Exception) {
                throw new ConnectionException($Exception -> getMessage(), $Exception -> getCode());
            }

            try {
                $this -> result = $this -> statement -> fetchAll(\PDO::FETCH_OBJ);
            } catch(\PDOException $e) {
                $this -> result = null;
            }

            $this -> statement -> closeCursor();
        }
        if(is_array($this -> result)) {
            if(count($this -> result) > 0) {
                return $this -> result;
            }
        }
        return true;
    }

    public function getError() {
        return $this -> connection -> errorInfo();
    }

    public function rollBack() {
        if($this -> connection != null) {
            $this -> connection -> rollBack();
        }
    }

    public function lastInsertId() {
        return $this -> connection -> lastInsertId();
    }

    private function beginTransaction() {
        try {
            if($this -> connection != null) {
                $this -> connection -> beginTransaction();
                return true;
            }
            return false;
        }
        catch(\PDOException $e) {
            return false;
        }
    }

    private function commit() {
        if(!$this -> beginTransaction()) {
            if($this -> connection != null) {
                $this -> connection -> commit();
            }
            return false;
        }
    }

    private function getTypeBindParam($type) {
        switch ($type) {
            case 'string':
            case 's':
            case 'char':
            case 'varchar':
            case 'date':
            case 'datetime':
            case 'float':
            case 'f':
            case 'double':
            case 'd':
            case 'decimal':
                return \PDO::PARAM_STR;
            case 'blob':
                return \PDO::PARAM_LOB;
            case 'i':
            case 'int':
            case 'integer':
            case 'number':
                return \PDO::PARAM_INT;
            case 'b':
            case 'bit':
                return \PDO::PARAM_BOOL;
        }
    }
}