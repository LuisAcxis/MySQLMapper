<?php

namespace AppsLine\MySQLMapper;

use AppsLine\MySQLMapper\Exception\ConnectionException;
use AppsLine\MySQLMapper\Connection;

class Config {

    private static $instance;

    public $connection;

    public static function getInstance() {
        if (!self::$instance instanceof self) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function setConnection($connection) {
        if(!$connection instanceof Connection) {
            throw new ConnectionException('La conexión no es de tipo AppsLine\MySQLMapper\Connection');
        }

        $this -> connection = $connection;
    }
}