<?php

class Usuario extends AppsLine\MySQLMapper\CRUD {

    const KEY_ENCRYPT_DB = 'k3y.3ncr1pt';
    const REGEX_CLEAN_NAME = '/[^a-zA-Z\s]/';
    const REGEX_VALIDATE_PASSWORD = '/^[a-zA-Z0-9].{5,}$/';
    const REGEX_VALIDATE_EMAIL = '/[-0-9a-zA-Z.+_]+@[-0-9a-zA-Z.+_]+.[a-zA-Z]{2,4}/';

    const DB_MODEL = [
        'table' => [
            'name' => 'usuarios'
        ],
        'columns' => [
            'id' => [
                'name' => 'id',
                'type' => 'int',
                'attributes' => [
                    'auto_increment'
                ]
            ],
            'nombre' => [
                'name' => 'nombre',
                'type' => 'varchar',
                'size' => 255,
                'attributes' => [
                    'not empty'
                ],
                'sanitize' => [
                    '__REGEX_CLEAN_NAME__'
                ],
            ],
            'apellido' => [
                'name' => 'apellido',
                'type' => 'varchar',
                'size' => 255,
                'attributes' => [
                    'not empty'
                ],
                'sanitize' => [
                    '__REGEX_CLEAN_NAME__'
                ],
            ],
            'foto_perfil' => [
                'name' => 'foto_perfil',
                'type' => 'varchar',
                'size' => 255,
                'attributes' => [
                    'default' => '__foto_perfil__',
                    'not empty'
                ]
            ],
            'correo' => [
                'name' => 'correo',
                'type' => 'varchar',
                'size' => 50,
                'attributes' => [
                    'not empty'
                ],
                'validate' => [
                    '__REGEX_VALIDATE_EMAIL__'
                ],
            ],
            'password' => [
                'name' => 'password',
                'type' => 'blob',
                'attributes' => [
                    'default' => '123456',
                    'not null',
                    'exclude'
                ],
                'validate' => [
                    '__REGEX_VALIDATE_PASSWORD__'
                ],
                'render' => [
                    'insert' => 'AES_ENCRYPT(__VALUE__, "__KEY_ENCRYPT_DB__")',
                    'update' => 'AES_ENCRYPT(__VALUE__, "__KEY_ENCRYPT_DB__")',
                    'select' => 'AES_DECRYPT(__THIS__, "__KEY_ENCRYPT_DB__")',
                    'where' => 'AES_ENCRYPT(__VALUE__, "__KEY_ENCRYPT_DB__")'
                ]
            ],
            'fecha_registro' => [
                'name' => 'fecha_registro',
                'type' => 'datetime',
                'attributes' => [
                    'default' => '__DATETIME__',
                    'not empty'
                ]
            ],
            'codigo' => [
                'name' => 'codigo',
                'type' => 'varchar',
                'size' => 6,
                'attributes' => [
                    'default' => '__FUNCTION(generateCode)__'
                ]
            ],
            'cantidad' => [
                'name' => 'cantidad',
                'type' => 'int',
                'attributes' => [
                    'default' => 5,
                    'not empty'
                ]
            ],
            'precio' => [
                'name' => 'precio',
                'type' => 'float',
                'attributes' => [
                    'not empty'
                ]
            ],
        ]
    ];

    function __construct($foto_perfil = null) {
        if($foto_perfil) {
            $this -> foto_perfil = $foto_perfil;
        } else {
            $this -> foto_perfil = 'profile.png';
        }
    }

    public function generateCode($schema, $column) {
        while (true) {
            $codeGenerate = '';
            $size = $column['size'];
            $pattern = [1,2,3,4,5,6,7,8,9,0];
            for($i=0; $i<$size; $i++) {
                $codeGenerate .= $pattern[mt_rand(0, count($pattern)-1)];
            }

            if(!self :: exist([$column['name'] => $codeGenerate])) {
                return $codeGenerate;
            }
        }
    }
}