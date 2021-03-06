<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit00dcae89e4bb05617f3b49460e2cc28d
{
    public static $prefixLengthsPsr4 = array (
        'A' => 
        array (
            'AppsLine\\MySQLMapper\\' => 21,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'AppsLine\\MySQLMapper\\' => 
        array (
            0 => __DIR__ . '/../..' . '/src',
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit00dcae89e4bb05617f3b49460e2cc28d::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit00dcae89e4bb05617f3b49460e2cc28d::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInit00dcae89e4bb05617f3b49460e2cc28d::$classMap;

        }, null, ClassLoader::class);
    }
}
