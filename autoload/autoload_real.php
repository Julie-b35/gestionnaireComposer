<?php

class ComposerAutoLoaderInit
{

    private static $loader;

    public static function loadClassLoader($class)
    {
        if ("Composer\Autoload\ClassLoader" === $class) {
            require(__DIR__ . DIRECTORY_SEPARATOR . 'ClassLoader.php');
        }

    }

    public static function getLoader()
    {
        if (null != self::$loader) {
            return self::$loader;
        }


        spl_autoload_register(['ComposerAutoLoaderInit', 'loadClassLoader'], true, true);
        self::$loader = $loader = new \Composer\Autoload\ClassLoader();
        spl_autoload_unregister(['ComposerAutoLoaderInit', 'loadClassLoader']);

        require(__DIR__ . DIRECTORY_SEPARATOR . 'autoload_static.php');
        call_user_func(\Composer\Autoload\ComposerStaticInit::getInitializer($loader));

        $loader->register(true);

        $filesToLoad = \Composer\Autoload\ComposerStaticInit::$files;
        $requireFile = \Closure::bind(static function ($fileIdentifier, $file) {
            if (empty($GLOBALS['__composer_autoload_files'][$fileIdentifier])) {
                $GLOBALS['__composer_autoload_files'][$fileIdentifier] = true;

                require($file);
            }
        }, null, null);
        foreach ($filesToLoad as $fileIdentifier => $file) {
            $requireFile($fileIdentifier, $file);
        }

        $mapPsr4 = require(__DIR__ . DIRECTORY_SEPARATOR . "autoload_psr4.php");
        foreach ($mapPsr4 as $nameSpace => $path) {
            $loader->setPsr4($nameSpace, $path);
        }
        return $loader;
    }
}