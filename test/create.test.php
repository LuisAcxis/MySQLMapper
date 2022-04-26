<?php
require_once(__DIR__ . '/conf/connection.php');
require_once(__DIR__ . '/models/Usuario.php');

use AppsLine\MySQLMapper\Test\Test;
use AppsLine\MySQLMapper\Exception\ConnectionException;
use AppsLine\MySQLMapper\Exception\ModelException;

Test :: create('testNombreNotInstance', function ($success, $error) {
    $usuario = new Usuario();
    $usuario -> apellido = 'Zavaleta';
    $usuario -> correo = 'testNombreNotEmpty@MySQLMapper.com';
    $usuario -> password = '123456';
    $usuario -> codigo = 12345;
    $usuario -> cantidad = 1;
    $usuario -> precio = 1.5;

    try {
        return $usuario -> create();
    } catch(ModelException $e) {
        switch ($e -> getCode()) {
            case ModelException :: ATTR_NOT_INSTANCE:
                $success();
                break;
            default:
                break;
        }
    } catch(Exception $e) {
        $error();
    }
})();

Test :: create('testSanitizeApellido', function ($success, $error) {
    $usuario = new Usuario();
    $usuario -> nombre = 'Luis';
    $usuario -> apellido = 'Zavaleta2022';
    $usuario -> correo = 'testSanitizeApellido@MySQLMapper.com';
    $usuario -> password = '123456';
    $usuario -> codigo = 12345;
    $usuario -> cantidad = 1;
    $usuario -> precio = 1.5;

    try {
        $usuarioId = $usuario -> create();
        $usuario -> id = $usuarioId;
        $usuario -> fetch();
        
        if($usuario -> apellido === 'Zavaleta') {
            $success();
        } else {
            $error();
        }
    } catch(Exception $e) {
        $error();
    }
})();

Test :: create('testCorreoValidate', function ($success, $error) {
    $usuario = new Usuario();
    $usuario -> nombre = 'Luis';
    $usuario -> apellido = 'Zavaleta';
    $usuario -> correo = 'testCorreoValidateMySQLMapper.com'; // correo no es correo
    $usuario -> password = '123456';
    $usuario -> codigo = 12345;
    $usuario -> cantidad = 1;
    $usuario -> precio = 1.5;

    try {
        return $usuario -> create();
    } catch(ModelException $e) {
        switch ($e -> getCode()) {
            case ModelException :: VALUE_IS_INVALID:
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

Test :: create('testNombreNotBeBoolean', function ($success, $error) {
    $usuario = new Usuario();
    $usuario -> nombre = false; // Error
    $usuario -> apellido = 'Zavaleta';
    $usuario -> correo = 'testNombreNotBeBoolean@MySQLMapper.com';
    $usuario -> password = '123456';
    $usuario -> codigo = 12345;
    $usuario -> cantidad = 1;
    $usuario -> precio = 1.5;

    try {
        return $usuario -> create();
    } catch(ModelException $e) {
        switch ($e -> getCode()) {
            case ModelException :: VALUE_NOT_BE_BOOLEAN:
                if($e -> getAttr() === 'nombre') {
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

Test :: create('testCantidadBeCero', function ($success, $error) {
    $usuario = new Usuario();
    $usuario -> nombre = 'Luis';
    $usuario -> apellido = 'Zavaleta';
    $usuario -> correo = 'testCantidadBeCero@MySQLMapper.com';
    $usuario -> password = '123456';
    $usuario -> codigo = 12345;
    $usuario -> cantidad = false; // 0
    $usuario -> precio = 1.5;

    try {
        $usuarioId = $usuario -> create();
        $usuario -> id = $usuarioId;
        $usuario -> fetch();

        if($usuario -> cantidad === 0) {
            $success();
        } else {
            $error();
        }
    } catch(Exception $e) {
        $error();
    }
})();

Test :: create('testCantidadBeUno', function ($success, $error) {
    $usuario = new Usuario();
    $usuario -> nombre = 'Luis';
    $usuario -> apellido = 'Zavaleta';
    $usuario -> correo = 'testCantidadBeUno@MySQLMapper.com';
    $usuario -> password = '123456';
    $usuario -> codigo = 12345;
    $usuario -> cantidad = true; // 1
    $usuario -> precio = 1.5;

    try {
        $usuarioId = $usuario -> create();
        $usuario -> id = $usuarioId;
        $usuario -> fetch();

        if($usuario -> cantidad === 1) {
            $success();
        } else {
            $error();
        }
    } catch(Exception $e) {
        $error();
    }
})();

Test :: create('testPrecioNotBeBoolean', function ($success, $error) {
    $usuario = new Usuario();
    $usuario -> nombre = 'Luis';
    $usuario -> apellido = 'Zavaleta';
    $usuario -> correo = 'testPrecioNotBeBoolean@MySQLMapper.com';
    $usuario -> password = '123456';
    $usuario -> codigo = 12345;
    $usuario -> cantidad = 3;
    $usuario -> precio = true; // Error
    try {
        return $usuario -> create();
    } catch(ModelException $e) {
        switch ($e -> getCode()) {
            case ModelException :: VALUE_NOT_BE_BOOLEAN:
                if($e -> getAttr() === 'precio') {
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

Test :: create('testPrecioBeFloat', function ($success, $error) {
    $usuario = new Usuario();
    $usuario -> nombre = 'Luis';
    $usuario -> apellido = 'Zavaleta';
    $usuario -> correo = 'testPrecioBeFloat@MySQLMapper.com';
    $usuario -> password = '123456';
    $usuario -> codigo = 12345;
    $usuario -> cantidad = 3;
    $usuario -> precio = '1.5'; // 1.5

    try {
        $usuarioId = $usuario -> create();
        $usuario -> id = $usuarioId;
        $usuario -> fetch();

        if($usuario -> precio === 1.5) {
            $success();
        } else {
            $error();
        }
    } catch(Exception $e) {
        $error();
    }
})();

Test :: create('testFechaRegistroIsInvalid', function ($success, $error) {
    $usuario = new Usuario();
    $usuario -> nombre = 'Luis';
    $usuario -> apellido = 'Zavaleta';
    $usuario -> correo = 'testFechaRegistroIsInvalid@MySQLMapper.com';
    $usuario -> password = '123456';
    $usuario -> codigo = 12345;
    $usuario -> cantidad = 3;
    $usuario -> precio = 1.5;
    $usuario -> fecha_registro = 'Hola'; // Error

    try {
        return $usuario -> create();
    } catch(ModelException $e) {
        switch ($e -> getCode()) {
            case ModelException :: VALUE_DATETIME_IS_INVALID:
                if($e -> getAttr() === 'fecha_registro') {
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

Test :: create('testFechaRegistroIsDatetime', function ($success, $error) {
    $usuario = new Usuario();
    $usuario -> nombre = 'Luis';
    $usuario -> apellido = 'Zavaleta';
    $usuario -> correo = 'testFechaRegistroIsDatetime@MySQLMapper.com';
    $usuario -> password = '123456';
    $usuario -> codigo = 12345;
    $usuario -> cantidad = 3;
    $usuario -> precio = 1.5;
    $usuario -> fecha_registro = '2022-01-01 00:00:01';

    try {
        $usuarioId = $usuario -> create();
        $success();
    } catch(Exception $e) {
        $error();
    }
})();

Test :: create('testFechaRegistroIsDate', function ($success, $error) {
    $usuario = new Usuario();
    $usuario -> nombre = 'Luis';
    $usuario -> apellido = 'Zavaleta123';
    $usuario -> correo = 'testFechaRegistroIsDate@MySQLMapper.com';
    $usuario -> password = '123456';
    $usuario -> codigo = 12345;
    $usuario -> cantidad = 3;
    $usuario -> precio = 1.5;
    $usuario -> fecha_registro = '2022-01-02';

    try {
        $usuarioId = $usuario -> create();
        $success();
    } catch(Exception $e) {
        $error();
    }
})();

Test :: create('testPasswordValidate', function ($success, $error) {
    $usuario = new Usuario();
    $usuario -> nombre = 'Luis';
    $usuario -> apellido = 'Zavaleta123';
    $usuario -> correo = 'testPasswordValidate@MySQLMapper.com';
    $usuario -> password = '12345'; // Error
    $usuario -> codigo = 12345;
    $usuario -> cantidad = 3;
    $usuario -> precio = 1.5;
    $usuario -> fecha_registro = '2022-01-02';

    try {
        return $usuario -> create();
    } catch(ModelException $e) {
        switch ($e -> getCode()) {
            case ModelException :: VALUE_IS_INVALID:
                if($e -> getAttr() === 'password') {
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

Test :: create('testSuccessCreate', function ($success, $error) {
    $usuario = new Usuario();
    $usuario -> nombre = 'Luis';
    $usuario -> apellido = 'Zavaleta123';
    $usuario -> correo = 'testSuccessCreate@MySQLMapper.com';
    $usuario -> password = '123456';
    $usuario -> codigo = 12345;
    $usuario -> cantidad = 3;
    $usuario -> precio = 1.5;

    try {
        $usuarioId = $usuario -> create();
        $success();
    } catch(Exception $e) {
        $error();
    }
})();

Test :: create('testIdExist', function ($success, $error) {
    $usuario = new Usuario();
    $usuario -> id = 90; // Error si ya existe
    $usuario -> nombre = 'Luis';
    $usuario -> apellido = 'Zavaleta123';
    $usuario -> correo = 'testIdExist@MySQLMapper.com';
    $usuario -> password = '123456';
    $usuario -> codigo = 12345;
    $usuario -> cantidad = 3;
    $usuario -> precio = 1.5;

    try {
        $usuarioId = $usuario -> create();
        $usuarioId = $usuario -> create();
        $error();
    } catch(ConnectionException $e) {
        switch ($e -> getCode()) {
            case ConnectionException :: DUPLICATE_KEY:
                $success();
                break;
            default:
                break;
        }
    } catch(Exception $e) {
        $error();
    }
})();

Test :: create('testCantidadDefault', function ($success, $error) {
    $usuario = new Usuario();
    $usuario -> nombre = 'Luis';
    $usuario -> apellido = 'Zavaleta123';
    $usuario -> correo = 'testCantidadDefault@MySQLMapper.com';
    $usuario -> password = '123456';
    $usuario -> codigo = 12345;
    $usuario -> precio = 1.5;

    try {
        $usuarioId = $usuario -> create();
        $usuario -> id = $usuarioId;
        $usuario -> fetch();

        if($usuario -> cantidad === 5) {
            $success();
        } else {
            $error();
        }
    } catch(Exception $e) {
        $error();
    }
})();

Test :: create('testCodigoDefault', function ($success, $error) {
    $usuario = new Usuario();
    $usuario -> nombre = 'Luis';
    $usuario -> apellido = 'Zavaleta123';
    $usuario -> correo = 'testCodigoDefault@MySQLMapper.com';
    $usuario -> password = '123456';
    $usuario -> codigo = '12345';
    $usuario -> precio = 1.5;

    try {
        $usuarioId = $usuario -> create();
        $usuario -> id = $usuarioId;
        $usuario -> fetch();

        if($usuario -> codigo === '12345') {
            $success();
        } else {
            $error();
        }
    } catch(Exception $e) {
        $error();
    }
})();

Test :: create('testCodigoDefaultBeString', function ($success, $error) {
    $usuario = new Usuario();
    $usuario -> nombre = 'Luis';
    $usuario -> apellido = 'Zavaleta123';
    $usuario -> correo = 'testCodigoDefaultBeString@MySQLMapper.com';
    $usuario -> password = '123456';
    $usuario -> codigo = 123456;
    $usuario -> precio = 1.5;

    try {
        $usuarioId = $usuario -> create();
        $usuario -> id = $usuarioId;
        $usuario -> fetch();

        if($usuario -> codigo === '123456') {
            $success();
        } else {
            $error();
        }
    } catch(Exception $e) {
        $error();
    }
})();

Test :: create('testCodigoFunction', function ($success, $error) {
    $usuario = new Usuario();
    $usuario -> nombre = 'Luis';
    $usuario -> apellido = 'Zavaleta123';
    $usuario -> correo = 'testCodigoFunction@MySQLMapper.com';
    $usuario -> password = '123456';
    $usuario -> precio = 1.5;

    try {
        $usuarioId = $usuario -> create();
        $usuario -> id = $usuarioId;
        $usuario -> fetch();

        if(is_numeric($usuario -> codigo)) {
            $success();
        } else {
            $error();
        }
    } catch(Exception $e) {
        $error();
    }
})();

Test :: create('testPasswordExist', function ($success, $error) {
    $usuario = new Usuario();
    $usuario -> nombre = 'Luis';
    $usuario -> apellido = 'Zavaleta123';
    $usuario -> correo = 'testPasswordExist@MySQLMapper.com';
    $usuario -> password = 'abcDEF123';
    $usuario -> precio = 1.5;

    try {
        $usuarioId = $usuario -> create();
        $usuario -> id = $usuarioId;
        
        $login = new Usuario();
        $login -> correo = 'testPasswordExist@MySQLMapper.com';
        $login -> password = 'abcDEF123';

        if($results = $login -> exist()) {
            if(is_array($results)) {
                $success();
            } else {
                $error();    
            }
        } else {
            $error();
        }
    } catch(Exception $e) {
        $error();
    }
})();

Test :: create('testFotoPerfilProperty', function ($success, $error) {
    $usuario = new Usuario('pruebaImagen.png');
    $usuario -> nombre = 'Luis';
    $usuario -> apellido = 'Zavaleta123';
    $usuario -> correo = 'testFotoPerfilProperty@MySQLMapper.com';
    $usuario -> password = '123456';
    $usuario -> precio = 1.5;

    try {
        $usuarioId = $usuario -> create();
        $usuario -> id = $usuarioId;
        $usuario -> fetch();

        if($usuario -> foto_perfil === 'pruebaImagen.png') {
            $success();
        } else {
            $error();
        }
    } catch(Exception $e) {
        $error();
    }
})();

Test :: create('testNombreNotEmpty', function ($success, $error) {
    $usuario = new Usuario();
    $usuario -> nombre = '';
    $usuario -> apellido = 'Zavaleta123';
    $usuario -> correo = 'testNombreNotEmpty@MySQLMapper.com';
    $usuario -> password = '123456';
    $usuario -> precio = 1.5;

    try {
        $usuario -> create();
    } catch(ModelException $e) {
        switch ($e -> getCode()) {
            case ModelException :: VALUE_IS_EMPTY:
                $success();
                break;
            default:
                break;
        }
    } catch(Exception $e) {
        $error();
    }
})();