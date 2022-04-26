<?php

namespace AppsLine\MySQLMapper\Exception;

use AppsLine\MySQLMapper\Exception\Exception;

class ConnectionException extends Exception {
    const DUPLICATE_KEY = 23000;

    public function __construct($message, $code = 0, Exception $previous = null) {
        parent :: __construct($message, $code, $previous);
    }
}