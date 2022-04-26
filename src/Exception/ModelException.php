<?php

namespace AppsLine\MySQLMapper\Exception;

use AppsLine\MySQLMapper\Exception\Exception;

class ModelException extends Exception {
    const ATTR_NOT_EXIST = 100;
    const ATTR_NOT_INSTANCE = 101;

    const METHOD_NOT_EXIST = 200;

    const VALUE_NOT_BE_NULL = 300;
    const VALUE_NOT_BE_BOOLEAN = 301;
    const VALUE_IS_INVALID = 302;
    const VALUE_EXCEEDS_SIZE = 303;
    const VALUE_IS_EMPTY = 304;
    const VALUE_DATETIME_IS_INVALID = 305;
    const VALUE_DATE_IS_INVALID = 306;

    private $attr;
    private $fullMessage;

    public function __construct($class, $application, $message, $code = 0, $attr, Exception $previous = null) {
        parent :: __construct($message, $code, $previous);
        
        $this -> fullMessage = $class . '::' . $application . ' - ' . $message;
        $this -> attr = $attr;
    }

    public function getAttr() {
        return $this -> attr;
    }

    public function getFullMessage() {
        return $this -> fullMessage;
    }
}