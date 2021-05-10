<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInitc6ff7e67f9b80dcd93587e1ea2b27fa3
{
    public static $prefixLengthsPsr4 = array (
        'M' => 
        array (
            'MakeitWorkPress\\WP_Updater\\' => 27,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'MakeitWorkPress\\WP_Updater\\' => 
        array (
            0 => __DIR__ . '/..' . '/makeitworkpress/wp-updater/src',
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInitc6ff7e67f9b80dcd93587e1ea2b27fa3::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInitc6ff7e67f9b80dcd93587e1ea2b27fa3::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInitc6ff7e67f9b80dcd93587e1ea2b27fa3::$classMap;

        }, null, ClassLoader::class);
    }
}
