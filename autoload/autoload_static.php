<?php

namespace Composer\Autoload;

class ComposerStaticInit
{
    /**
     * @var array<string, string>
     */
    public static $files = [];

    /**
     * 
     * @var array<string, string>
     */
    public static $classMap = [];


    public static function getInitializer(ClassLoader $loader): \Closure
    {
        return \Closure::bind(function () use ($loader) {
            $loader->classMap = ComposerStaticInit::$classMap;
        }, null, ClassLoader::class);
    }
}
?>