<?php
require_once(__DIR__ . '/conf/connection.php');
require_once(__DIR__ . '/models/Usuario.php');

use AppsLine\MySQLMapper\Test\Test;
use AppsLine\MySQLMapper\Exception\ModelException;

Test :: create('testCorreoNotBeBoolean', function ($success, $error) {
    $usuario = new Usuario();
    $usuario -> correo = true;
    $usuario -> password = '123456';

    try {
        return $usuario -> list();
    } catch(ModelException $e) {
        switch ($e -> getCode()) {
            case ModelException :: VALUE_NOT_BE_BOOLEAN:
                if($e -> getAttr() === 'correo') {
                    $success();
                }
                break;
            default:
                break;
        }
    } catch(Exception $e) {
        $error();
    }
})();