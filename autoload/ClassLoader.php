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
     * @var array[]
     * @psalm-var array<string, array<string, int>>
     */
    private array $prefixLengthsPsr4 = [];

    /**
     * @var array[]
     * @psalm-var array<string, array<int, string>>
     */
    private array $prefixDirsPsr4 = [];

    /**
     * @var array[]
     * @psalm-var array<string, string>
     */
    private array $fallbackDirsPsr4 = [];

    /**
     * @var array[]
     * @psalm-var array<string, array<string, string[]>>
     */
    private array $prefixesPsr0 = [];

    /**
     * @var array[]
     * @psalm-var array<string, string>
     */
    private array $fallbackDirsPsr0 = [];

    /**
     * @var bool
     */
    private bool $useIncludePath = false;

    /**
     * @var array[]
     * @psalm-var array<string, string>
     */
    private array $classMap = [];

    /**
     * @var bool
     */
    private bool $classMapAuthoritative = false;

    /**
     * @var array[]
     * @psalm-var array<string, bool>
     */
    private array $missingClasses = [];

    /**
     * @var ?string
     */
    private ?string $apcuPrefix = null;

    /**
     * @var self[]
     */
    private static array $registeredLoaders = [];

    /**
     * @param string|null $vendorDir
     */
    public function __construct(?string $vendorDir = null)
    {
        $this->vendorDir = $vendorDir;
        self::initializeIncludeClosure();
    }
    //=============================== GETTERS ============================================
    /**
     * @return string[]
     */
    public function getPrefixes(): array
    {
        if (isset($this->prefixesPsr0) && count($this->prefixesPsr0) === 0) {
            if (is_array($ret = call_user_func_array('array_merge', array_values($this->prefixesPsr0)))) {
                return $ret;
            }
        }
        return [];
    }

    /**
     * @return array[]
     * @psalm-return array<string, array<int, string>>
     */
    public function getPrefixesPsr4(): array
    {
        return $this->prefixDirsPsr4;
    }

    /**
     * @return array[]
     * @psalm-return array<string, string>
     */
    public function getFallbackDirs(): array
    {
        return $this->fallbackDirsPsr0;
    }

    /**
     * @return array[]
     * @psalm-return array<string, string>
     */
    public function getFallbackDirsPsr4(): array
    {
        return $this->fallbackDirsPsr4;
    }

    /**
     * @return string[] Tableau de nom de classe => chemin du fichier contenant la classe.
     * @psalm-return array<string, string>
     */
    public function getClassMap(): array
    {
        return $this->classMap;
    }

    /**
     * Peut être utilisé pour contrôler si l'autoloader utilise 
     * la règle d'inclusion de chemin pour les classes.
     * 
     * @return bool
     */
    public function getUseIncludePath(): bool
    {
        return $this->useIncludePath;
    }

    /**
     * La recherche de classe doit-elle échouer si elle n'est pas trouvée dans le tableau classMap ?
     * 
     * @return bool
     */
    public function isClassMapAuthoritative(): bool
    {
        return $this->classMapAuthoritative;
    }

    /**
     * Le préfixe APCu utilisé, ou nul si la mise en cache APCu n'est pas activée.
     * 
     * @return ?string
     */
    public function getApcuPrefix(): ?string
    {
        return $this->apcuPrefix;
    }

    /**
     * Renvoie les chargeurs actuellement enregistrés indexés par leurs répertoires de fournisseurs correspondants.
     * @return self[]
     */
    public static function getRegisteredLoader(): array
    {
        return self::$registeredLoaders;
    }
    //============================================== SETTERS ================================================

    /**
     * @param string[] $classMap
     * @psalm-param array<string, string> $classMap
     * 
     * @return void
     */
    public function addClassMap(array $classMap): void
    {
        trigger_error('Revenir à la fonction addClassMap()');
    }

    /**
     * Définis un ou plusieurs chemins depuis un préfixe donnée selon la norme PSR-0
     * ou bien ajouter en début ou fin de liste sur l'un des préfixe précédemment définis.
     * 
     * @param string            $prefix     Le préfixe
     * @param string[]|string   $paths      Le répertoire principal PSR-0
     * @param bool              $prepend    Si le ou les répertoires doivent être mise en dessus de la pile.
     * 
     * @return void
     */
    public function add(string $prefix, $paths, bool $prepend = false)
    {
        trigger_error('Revenir à la fonction add()');
    }

    /**
     * Définis un ou plusieurs chemins depuis un préfixe donnée selon la norme PSR-4
     * ou bien ajouter en début ou fin de liste sur l'un des préfixe précédemment définis.
     * 
     * @param string            $prefix     Le préfixe/espace de nom avec les antislashes '\\'
     * @param string[]|string   $paths      Le répertoire principal PSR-4
     * @param bool              $prepend    Si le ou les répertoires doivent être mise en dessus de la pile.
     * 
     * @return void
     */
    public function addPsr4(string $prefix, $paths, bool $prepend = false)
    {
        trigger_error('Revenir à la fonction addPsr4()');
    }

    /**
     * Définis un ou plusieurs chemins depuis un préfixe donnée selon la norme PSR-0
     * ou bien remplace  un préfixe précédemment définis.
     * 
     * @param ?string           $prefix     Le préfixe
     * @param string[]|string   $paths      Le répertoire principal PSR-0
     * 
     * @return void
     */
    public function set(?string $prefix, $paths)
    {
        trigger_error('Revenir à la fonction set()');
    }

    /**
     * Définis un ou plusieurs chemins depuis un préfixe donnée selon la norme PSR-0
     * ou bien remplace  un préfixe précédemment définis.
     * 
     * @param ?string           $prefix     Le préfixe
     * @param string[]|string   $paths      Le répertoire principal PSR-0
     * 
     * @return void
     */
    public function setPsr4(?string $prefix, $paths)
    {
        if (is_null($prefix)) {
            $this->fallbackDirsPsr4 = (array) $paths;
        } else {
            $length = strlen($prefix);
            if ('\\' !== $prefix[$length - 1]) {
                throw new \InvalidArgumentException("Un préfixe PSR-4 non vide doit ce terminer par un antislashes '\\'.");
            }
            $this->prefixLengthsPsr4[$prefix[0]][$prefix] = $length;
            $this->prefixDirsPsr4[$prefix] = (array) $paths;
        }
    }

    /**
     * Active la recherche du chemin à inclure pour les fichiers de classes.
     * 
     * @param bool $useIncludePath
     * 
     * @return void
     */
    public function setUseIncludePath(bool $useIncludePath): void
    {
        $this->useIncludePath = $useIncludePath;
    }

    /**
     * Désactive la recherche par préfixe et répertoire "fallback" pour les classes
     * qui n'ont pas été enregistré avec classMap
     * 
     * @param bool $classMapAuthoritative
     * 
     * @return void
     */
    public function setClassMapAuthoritative(bool $classMapAuthoritative): void
    {
        trigger_error('Revenir à la fonction setClassMapAuthoritative()');
    }


    /**
     * Préfixe APCu à utiliser pour mettre en cache les classes trouvées/non trouvées, si l'extension est activée.
     * 
     * @param ?string $apcuPrefix
     * 
     * @return void
     */
    public function setApcuPrefix(?string $apcuPrefix): void
    {
        $this->apcuPrefix = function_exists('apcu_fetch') && filter_var(ini_get('apc.enabled'), FILTER_VALIDATE_BOOLEAN) ? $apcuPrefix : null;
    }

    /**
     * Enregistre cette instance comme un autoloader
     * @param bool  $prepend Si l'instance doit mise au début de la pile des autoloader.
     * 
     * @return void
     */
    public function register(bool $prepend = false): void
    {
        // @phpstan-ignore-next-line
        spl_autoload_register([$this, 'loadClass'], true, $prepend);

        if (null === $this->vendorDir) {
            return;
        }
        trigger_error('Revenir à la fonction register(bool $prepend = false)');
    }

    /**
     * Supprime cette instance comme un autoloader
     * 
     * @return void
     */
    public function unregister(): void
    {
        spl_autoload_unregister([$this, 'loadClass']);

        if (null !== $this->vendorDir) {
            unset(self::$registeredLoaders[$this->vendorDir]);
        }
    }

    public function loadClass(string $class): bool
    {
        $file = $this->findFile($class);
        if (is_string($file) && $file !== '') {
            $includeFile = self::$includeFile;
            if (is_callable($includeFile))
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
        if ($this->useIncludePath) {
            $file = stream_resolve_include_path($logicalPathPsr0);
            if (is_string($file) && $file !== '')
                return $file;
        }

        return false;
    }

    private static function initializeIncludeClosure(): void
    {
        if (isset(self::$includeFile)) {
            return;
        }

        self::$includeFile = \Closure::bind(static function ($file) {
            include($file);
        }, null, null);
    }
}