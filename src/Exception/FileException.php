<?php

namespace AppsLine\MySQLMapper\Exception;

use AppsLine\MySQLMapper\Exception\Exception;

class FileException extends Exception {
    public function __construct($message, $code = 0, Exception $previous = null) {
        parent :: __construct($message, $code, $previous);
    }
}