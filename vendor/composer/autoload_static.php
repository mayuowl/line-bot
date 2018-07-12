<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit270d14e85e1647b9a4254d6dee037c1c
{
    public static $prefixLengthsPsr4 = array (
        'L' => 
        array (
            'LINE\\' => 5,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'LINE\\' => 
        array (
            0 => __DIR__ . '/..' . '/linecorp/line-bot-sdk/src',
        ),
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit270d14e85e1647b9a4254d6dee037c1c::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit270d14e85e1647b9a4254d6dee037c1c::$prefixDirsPsr4;

        }, null, ClassLoader::class);
    }
}
