<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInitbea9b2ca50508341bd1ad36c2c33e11a
{
    public static $prefixLengthsPsr4 = array (
        'V' => 
        array (
            'Validator\\Language\\' => 19,
            'Validator\\' => 10,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Validator\\Language\\' => 
        array (
            0 => __DIR__ . '/..' . '/validator/src/language',
        ),
        'Validator\\' => 
        array (
            0 => __DIR__ . '/..' . '/validator/src/validator',
        ),
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInitbea9b2ca50508341bd1ad36c2c33e11a::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInitbea9b2ca50508341bd1ad36c2c33e11a::$prefixDirsPsr4;

        }, null, ClassLoader::class);
    }
}
