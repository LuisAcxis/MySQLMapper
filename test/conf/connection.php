<?php
require_once(__DIR__ . '/../../vendor/autoload.php');

use AppsLine\MySQLMapper\File;

$reflection = new \ReflectionClass(\Composer\Autoload\ClassLoader::class);
define("ROOT_PATH", dirname($reflection->getFileName(), 3));

$fileConfig = file_get_contents(ROOT_PATH . '/test/conf/config.json');
if ($fileConfig === false) {
    echo 'Archivo "/test/conf/config.json no configurado"';
    exit;
}

$jsonConfig = json_decode($fileConfig);

$host = $jsonConfig -> host;
$user = $jsonConfig -> user;
$password = $jsonConfig -> password;
$database = $jsonConfig -> database;

AppsLine\MySQLMapper\Config :: getInstance() -> setConnection(new AppsLine\MySQLMapper\Connection([
    'host' => $host,
    'user' => $user,
    'password' => $password,
    'onLoad' => function($connection) use ($database) {
        $connection -> execute('CREATE DATABASE IF NOT EXISTS ' . $database);
        $connection -> execute('USE ' . $database);

        $path = ROOT_PATH . '/test/sql/create.default.sql';

        $file = new File($path);
        foreach($file -> read() as $sql) {
            $connection -> execute($sql);
        }
    },
    'onUnload' => function($connection) use ($database) {
        $connection -> execute('DROP DATABASE IF EXISTS ' . $database);
    },
]));