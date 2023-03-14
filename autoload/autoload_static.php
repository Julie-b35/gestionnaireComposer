<?php

namespace Composer\Autoload;

class ComposerStaticInit
{
    public static $files = [];

    public static $classMap = [];
    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->classMap = ComposerStaticInit::$classMap;
        }, null, ClassLoader::class);
    }
}
?>