<?php

/*
 * Ceci est fait partie de Composer
 *
 * (c) Nils Adermann <naderman@naderman.de>
 *     Jordi Boggiano <j.boggiano@seld.be>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Composer\Autoload;

/**
 * Summary of ClassLoader
 */
class ClassLoader
{

    /**
     * @var \Closure(string):void
     */
    private static ?\Closure $includeFile;

    /**
     * @var ?string
     */
    private ?string $vendorDir;

    /**
     * @var array<string, array<string,int>>
     */
    private array $prefixLengthsPsr4 = [];

    /**
     * @var array
     */
    private array $prefixDirsPsr4 = [];

    private $fallbackDirsPsr4 = [];

    private $prefixesPsr0 = [];

    private $fallbackDirsPsr0 = [];

    private $useIncludePath = false;

    private $classMap = [];

    private $classMapAuthoritative = false;

    private $missingClasses = [];

    private $apcuPrefix;

    private static $registeredLoaders = [];

    public function __construct(?string $vendorDir = null)
    {
        $this->vendorDir = $vendorDir;
        self::initializeIncludeClosure();
    }

    public function setPsr4(string $prefix, string $path)
    {
        if (!$prefix) {
            $this->fallbackDirsPsr4 = (array) $path;
        } else {
            $length = strlen($prefix);
            if ('\\' !== $prefix[$length - 1]) {
                throw new \InvalidArgumentException("Un préfixe PSR-4 non vide doit ce terminer par un antislashes '\\'.");
            }
            $this->prefixLengthsPsr4[$prefix[0]][$prefix] = $length;
            $this->prefixDirsPsr4[$prefix] = (array) $path;
        }
    }
    public function register(bool $prepend = false)
    {
        spl_autoload_register([$this, 'loadClass'], true, $prepend);

        if (null === $this->vendorDir) {
            return;
        }
        trigger_error('Revenir à la fonction register(bool $prepend = false)');
    }

    public function loadClass(string $class)
    {
        if ($file = $this->findFile($class)) {
            $includeFile = self::$includeFile;
            $includeFile($file);

            return true;
        }

        return false;
    }

    /**
     * 
     * @param string $class
     * @return string|false
     */
    public function findFile(string $class): string|false
    {
        if (isset($this->classMap[$class])) {
            return $this->classMap[$class];
        }

        if ($this->classMapAuthoritative || isset($this->missingClasses[$class])) {
            return false;
        }

        if (null !== $this->apcuPrefix) {
            trigger_error('Revenir à la fonction findFile(string $class)');
        }

        $file = $this->findFileWithExtension($class, '.php');

        if (false === $file && defined("HHVM_VERSION")) {
            trigger_error('Revenir à la fonction findFile(string $class)');
        }

        if (false === $file) {
            $this->missingClasses[$class] = true;
        }

        return $file;
    }

    /**
     * @param string $class
     * @param string $ext
     * @return string|false
     */
    private function findFileWithExtension(string $class, string $ext): string|false
    {
        $logicalPathPsr4 = strtr($class, '\\', DIRECTORY_SEPARATOR) . $ext;

        $first = $class[0];
        if (isset($this->prefixLengthsPsr4[$first])) {
            $subPath = $class;
            while (false !== $lastPos = strrpos($subPath, '\\')) {
                $subPath = substr($subPath, 0, $lastPos);
                $search = $subPath . '\\';
                if (isset($this->prefixDirsPsr4[$search])) {
                    $pathEnd = DIRECTORY_SEPARATOR . substr($logicalPathPsr4, $lastPos + 1);
                    foreach ($this->prefixDirsPsr4[$search] as $dir) {
                        $file = $dir . $pathEnd;
                        //var_dump($file);
                        if (file_exists($file)) {
                            return $file;
                        }
                    }
                }
            }
        }

        //PSR-4 fallback dirs
        foreach ($this->fallbackDirsPsr4 as $dir) {
            trigger_error('Revenir à la fonction findFileWidthExtension(string $class, string $ext)');
        }

        //PSR-0 lookup
        if (false !== $pos = strrpos($class, '\\')) {
            $logicalPathPsr0 = substr($logicalPathPsr4, 0, $pos + 1)
                . strtr(substr($logicalPathPsr4, $pos + 1), '_', DIRECTORY_SEPARATOR);
            //var_dump($logicalPathPsr0);
        } else {
            $logicalPathPsr0 = "";
            trigger_error('Revenir à la fonction findFileWidthExtension(string $class, string $ext)');
        }

        if (isset($this->prefixesPsr0[$first])) {
            trigger_error('Revenir à la fonction findFileWidthExtension(string $class, string $ext)');
        }

        //PSR-0 fallback dirs
        foreach ($this->fallbackDirsPsr0 as $dir) {
            trigger_error('Revenir à la fonction findFileWidthExtension(string $class, string $ext)');
        }

        //PSR-0 include path
        if ($this->useIncludePath && $file = stream_resolve_include_path($logicalPathPsr0)) {
            return $file;
        }

        return false;
    }

    private static function initializeIncludeClosure()
    {
        if (isset(self::$includeFile) && self::$includeFile !== null) {
            return;
        }

        self::$includeFile = \Closure::bind(static function ($file) {
            include($file);
        }, null, null);
    }
}