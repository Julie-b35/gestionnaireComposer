<?php declare(strict_types=1);

/*
 * Ce fichier fait partie du projet: composer/xdebug-handler.
 *
 * (c) Composer <https://github.com/composer>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace Composer\XdebugHandler;

use Composer\Pcre\Preg;
use Psr\Log\LoggerInterface;

/**
 * Variables Environnement :
 * - MY_APP_ALLOW_XDEBUG (MY_APP étant le nom de mon appli.) - Empêche le redémarrage du processus et garde Xdebug.
 * @author John Stevenson <john-stevenson@blueyonder.co.uk>
 *
 * @phpstan-import-type restartData from PhpConfig
 */
class XdebugHandler
{
    const SUFFIX_ALLOW = '_ALLOW_XDEBUG';

    const SUFFIX_INI = '_ORIGINAL_INIS';

    const RESTART_ID = 'INTERNAL';

    const RESTART_SETTINGS = 'XDEBUG_HANDLER_SETTINGS';

    const DEBUG = 'XDEBUG_HANDLER_DEBUG';

    /**
     * @var string|null
     */
    protected ?string $tmpIni;

    /**
     * @var bool
     */
    private static bool $inRestart;

    /**
     * @var string
     */
    private static string $name;

    /**
     * @var string|null
     */
    private static ?string $skipped;

    /**
     * @var bool
     */
    private static bool $xdebugActive;

    /**
     * @var string|null
     */
    private static ?string $xdebugMode;

    /**
     * @var string|null
     */
    private static ?string $xdebugVersion;

    /**
     * @var bool
     */
    private bool $cli;

    /**
     * @var string|null
     */
    private ?string $debug;

    /**
     * @var string
     */
    private string $envAllowXdebug;

    /**
     * @var string
     */
    private string $envOriginalIni;

    /**
     * @var bool
     */
    private bool $persistant;

    /**
     * @var string|null
     */
    private ?string $script;

    /**
     * @var Status
     */
    private Status $statusWriter;

    public function __construct(string $envPrefix)
    {
        if ($envPrefix === '')
            throw new \RuntimeException("Paramètre constructeur invalide.");

        self::$name = strtoupper($envPrefix);
        $this->envAllowXdebug = self::$name . self::SUFFIX_ALLOW;
        $this->envOriginalIni = self::$name . self::SUFFIX_INI;

        self::setXdebugDetail();
        self::$inRestart = false;

        if ($this->cli = PHP_SAPI === 'cli')
            $this->debug = (string) getenv(self::DEBUG);


        $this->statusWriter = new Status($this->envAllowXdebug, (bool) $this->debug);
    }

    public function getAll(): void
    {
        var_dump([
            "self::\$skipped" => self::$skipped,
        ]);
    }



    public function noCalledFunction(): void
    {
        $this->syncSettings([
            "tmpIni" => "",
            "scannedIni" => false,
            "scanDir" => "",
            "phprc" => false,
            "inis" => [""],
            "skipped" => "",
        ]);
    }
    //========================================== SETTERS ======================================================
    /**
     * Redirige les message vers un logger PSR-3
     *
     * @param LoggerInterface $logger
     *
     * @return XdebugHandler
     */
    public function setLogger(LoggerInterface $logger): self
    {
        $this->statusWriter->setLogger($logger);
        return $this;
    }

    /**
     * Définis l'emplacement du scripte principal si il n'est pas appelé depuis argv
     * @param string $script
     * @return XdebugHandler
     */
    public function setMainScript(string $script): self
    {
        $this->script = $script;
        return $this;
    }

    /**
     * Conserve les paramètres pour garder Xdebug hors des sous-processus
     *
     * @return XdebugHandler
     */
    public function setPersistant(): self
    {
        $this->persistant = true;
        return $this;
    }

    /**
     * Vérifie si Xdebug est chargé et si le processus à besoin d'être démarré.
     *
     * Ce comportement peut être désactivé en définissant la variable d'environnement MY_APP_ALLOW_XDEBUG à 1.
     * Cette variable est utilisé en interne afin que le processus démarré ne le sois qu'une seule fois.
     *
     * @return void
     */
    public function check(): void
    {
        $this->notify(Status::CHECK, self::$xdebugVersion . '|' . self::$xdebugMode);
        $envArgs = explode('|', (string) getenv($this->envAllowXdebug));

        if (!((bool) $envArgs[0]) && $this->requireRestart(self::$xdebugActive)) {
            //redémarrage requis
            $this->notify(Status::RESTART);

            if ($this->prepareRestart()) {
                $command = $this->getCommand();
                $this->restart($command);
            }
            return;
        }

        if (self::RESTART_ID === $envArgs[0] && count($envArgs) === 5) {
            //Redémarré, donc non défini la variable d'environnement et utilise les valeurs enregistrées
            $this->notify(Status::RESTARTED);

            Process::setEnv($this->envAllowXdebug);
            self::$inRestart = true;

            if (is_null(self::$xdebugVersion)) {
                //La version ignorée n'est définie que si Xdebug n'est pas chargé
                self::$skipped = $envArgs[1];
            }

            $this->tryEnableSignals();

            //Mettre les paramètres de redémarrage dans l'environnement
            $this->setEnvRestartSettings($envArgs);

            return;
        }

        $this->notify(Status::NO_RESTART);
        $settings = self::getRestartSettings();

        if (!is_null($settings)) {
            trigger_error('Revenir à la fonction check()');
        }
    }

    /**
     * Renvoie un tableau d'emplacement de fichier de configuration php.ini avec au moins une entré
     *
     * L'équivalent de l'appel à la fonction <php_ini_loaded_file()>, et <php_ini_scanned_files()>.
     * L'emplacement de fichier Ini chargé, est la première entré de la liste, et peut être vide.
     *
     * @return string[]
     */
    public static function getAllIniFiles(): array
    {
        if (isset(self::$name)) {
            $env = getenv(self::$name . self::SUFFIX_INI);

            if (false !== $env) {
                return explode(PATH_SEPARATOR, $env);
            }
        }

        $paths = [(string) php_ini_loaded_file()];
        $scanned = php_ini_scanned_files();

        if ($scanned !== false) {
            $paths = array_merge($paths, array_map('trim', explode(',', $scanned)));
        }
        return $paths;
    }

    /**
     * Renvoie un tableau de configuration pour le redémarrage
     *
     * Les paramètres seront disponibles si le processus en cours a été redémarré
     * ou appelé avec les paramètres d'un redémarrage existant.
     *
     * @phpstan-return restartData|null
     */
    public static function getRestartSettings(): ?array
    {
        $envArgs = explode('|', (string) getenv(self::RESTART_SETTINGS));

        if (
            count($envArgs) !== 6
            || (!self::$inRestart && php_ini_loaded_file() !== $envArgs[0])
        ) {
            return null;
        }

        return [
            "tmpIni" => $envArgs[0],
            "scannedIni" => (bool) $envArgs[1],
            "scanDir" => '*' === $envArgs[2] ? false : $envArgs[2],
            "phprc" => '*' === $envArgs[3] ? false : $envArgs[3],
            "inis" => explode(PATH_SEPARATOR, $envArgs[4]),
            "skipped" => $envArgs[5],
        ];
    }

    public static function getSkippedVersion(): string
    {
        return (string) self::$skipped;
    }

    /**
     * Renvoie si Xdebug est chargé et actif.
     *
     * true: Si Xdebug est chargé et lancé avec une mode actif.
     * false: Si Xdebug n'est pas chargé, ou si il est lancé avec xdebug.mode=off.
     *
     * @return bool
     */
    public static function isXdebugActive(): bool
    {
        self::setXdebugDetail();
        return self::$xdebugActive;
    }

    /**
     * Permet à une classe d'extension de décider s'il doit y avoir un redémarrage
     *
     * La valeur par défaut est de redémarrer si Xdebug est chargé et que son mode n'est pas "off".
     *
     * fonction à écrasé.
     * @param bool $default
     * @return bool
     * @abstract
     */
    protected function requireRestart(bool $default): bool
    {
        return $default;
    }

    /**
     * Permet à une classe d'extension d'accéder au tmpIni
     *
     * @param string[] $command
     *
     * @return never
     * @abstract
     */
    protected function restart(array $command): void
    {
        $this->doRestart($command);
    }
    /**
     * Exécute la commande redémarrée puis supprime le tmp ini
     *
     * @param string[] $command
     *
     * @return never
     */
    private function doRestart(array $command): void
    {
        $this->tryEnableSignals();
        $this->notify(Status::RESTARTING, implode(' ', $command));

        if (PHP_VERSION_ID >= 70400) {
            $cmd = $command;
        } else {
            $cmd = Process::escapeShellCommand($command);
            if (defined('PHP_WINDOWS_VERSION_BUILD')) {
                //Guillemets extérieurs requis sur la chaîne cmd sous PHP 8
                $cmd = '"' . $cmd . '"';
            }
        }

        $process = proc_open($cmd, [], $pipes);
        if (is_resource($process)) {
            $exitCode = proc_close($process);
        }

        if (!isset($exitCode)) {
            //Il est peu probable que php ou le shell par défaut ne puisse pas être invoqué
            $this->notify(Status::ERROR, 'Impossible de redémarré le processus');
            $exitCode = -1;
        } else {
            $this->notify(Status::INFO, 'Processus de redémarrage terminé avec code de retour : ' . $exitCode);
        }

        if ($this->debug === '2') {
            $this->notify(Status::INFO, 'Fichier temporaire ini sauvegardé : ' . $this->tmpIni);
        } else {
            if (isset($this->tmpIni) && file_exists($this->tmpIni)) {
                unlink($this->tmpIni);
            }
        }
        exit($exitCode);
    }

    /**
     * Renvoie true si tout a été écrit pour le redémarrage
     * Si l'un des éléments suivants échoue (même si peu probable), nous devons renvoyer false pour arrêter la récursivité potentielle :
     * - création de fichier ini tmp
     * - création de variables d'environnement
     *
     * @return bool
     */
    private function prepareRestart(): bool
    {
        $error = null;
        $iniFiles = self::getAllIniFiles();
        $scannedInis = count($iniFiles) > 1;
        $tmpDir = sys_get_temp_dir();

        if (!$this->cli) {
            $error = 'SAPI (' . PHP_SAPI . ') non supporté.';
        } elseif (!$this->checkConfiguration($info)) {
            $error = $info;
        } elseif (!$this->checkMainScript()) {
            if (isset($this->script))
                $error = 'Impossible d\'accéder au script principale : ' . $this->script;
            else
                $error = 'Impossible d\'accéder au script principale : \'paramètre non définie.\'';
        } elseif (!$this->writeTmpIni($iniFiles, $tmpDir, $error)) {
            $error = is_null($error) ? 'Impossible de crée le fichier ini temporaire dans le dossier : ' . $tmpDir : $error;
        } elseif (!$this->setEnvironment($scannedInis, $iniFiles)) {
            $error = 'Impossible de définir les variables d’environnement.';
        }

        if (!is_null($error)) {
            $this->notify(Status::ERROR, $error);
        }
        return is_null($error);
    }

    /**
     * Renvoie true si le fichier ini est bien écris.?
     *
     * @param string[] $iniFiles Tous les fichiers ini utilisé dans le processus courant.
     * @param string $tmpDir
     * @param string|null $error
     *
     * @return bool
     */
    private function writeTmpIni(array $iniFiles, string $tmpDir, ?string &$error): bool
    {
        if (($tmpFile = @tempnam($tmpDir, '')) === false) {
            return false;
        }

        $this->tmpIni = $tmpFile;

        //$iniFiles a au moins un élément et il peut être vide
        if ($iniFiles[0] == '') {
            array_shift($iniFiles);
        }

        $content = '';
        $sectionRegex = '/^\s*\[(?:PATH|HOST)\s*=/mi';
        $xdebugRegex = '/^\s*(zend_extension\s*=.*xdebug.*)$/mi';

        foreach ($iniFiles as $file) {
            //Rechercher les fichiers ini inaccessibles
            if (($data = @file_get_contents($file)) === false) {
                $error = 'Impossible de lire le fichier ini: ' . $file;
                return false;
            }

            //Vérifier et supprimer les directives après les sections HOST et PATH
            /** @var non-empty-string $data */
            if (Preg::isMatchWithOffsets($sectionRegex, $data, $matches, PREG_OFFSET_CAPTURE)) {
                $data = substr($data, 0, $matches[0][1]);
            }
            $replaceXdebug = Preg::replace($xdebugRegex, ";$1", $data);

            if (is_string($replaceXdebug)) {
                $content .= $replaceXdebug . PHP_EOL;
            } else {
                var_dump($replaceXdebug);
                throw new \UnexpectedValueException("La méthode 'replace' de la classe Preg à renvoyer un tableau au lieu d'une chaîne.");
            }
        }
        //Fusionner les paramètres chargés dans notre contenu ini, s'il est valide
        $config = parse_ini_string($content);
        $loaded = ini_get_all(null, false);

        if (false === $config || false === $loaded) {
            $error = 'Impossible d\'analyser les donnée ini.';
            return false;
        }

        $content .= $this->mergeLoadedConfig($loaded, $config);

        // Work-around for https://bugs.php.net/bug.php?id=75932
        $content .= 'opcache.enable_cli=0' . PHP_EOL;

        return (bool) @file_put_contents($tmpFile, $content);
    }

    /**
     * Renvoie les arguments de la ligne de commande pour le redémarrage
     *
     * @return string[]
     */
    private function getCommand(): array
    {
        $php = [PHP_BINARY];
        $args = array_slice($_SERVER['argv'], 1);

        return array_merge($php, [$this->script], $args);
    }

    /**
     * Renvoie true si les variables d'environnement de redémarrage ont été définies
     *
     * Pas besoin de mettre à jour $_SERVER puisque cela est défini dans le processus redémarré.
     *
     * @param bool $scannedInis
     * @param string[] $iniFiles
     * @return bool
     */
    private function setEnvironment(bool $scannedInis, array $iniFiles): bool
    {
        $scanDir = getenv('PHP_INI_SCAN_DIR');
        $phprc = getenv('PHPRC');

        //Rendre le fichier inis d'origine disponible pour le processus redémarré
        if (!putenv($this->envOriginalIni . '=' . implode(PATH_SEPARATOR, $iniFiles))) {
            return false;
        }

        if (isset($this->persistant) && $this->persistant) {
            //Utiliser l'environnement pour conserver les paramètres
            if (!putenv('PHP_INI_SCAN_DIR=') || !putenv('PHPRC=' . $this->tmpIni)) {
                return false;
            }
        }

        //Marquer le processus redémarré et enregistrer les valeurs à utiliser
        $envArgs = [
            self::RESTART_ID,
            self::$xdebugVersion,
            (int) $scannedInis,
            false === $scanDir ? '*' : $scanDir,
            false === $phprc ? '*' : $phprc
        ];

        return putenv($this->envAllowXdebug . '=' . implode('|', $envArgs));
    }

    private function notify(string $op, ?string $data = null): void
    {
        $this->statusWriter->report($op, $data);
    }

    /**
     * Renvoie les paramètres ini par défaut, modifiés et de ligne de commande
     *
     * @param mixed[] $loadedConfig Toute la configuration ini
     * @param mixed[] $iniConfig configuration depuis les fichiers ini utilisateur.
     *
     * @return string
     */
    private function mergeLoadedConfig(array $loadedConfig, array $iniConfig): string
    {
        $content = '';

        foreach ($loadedConfig as $name => $value) {
            if (
                !is_string($value)
                || strpos($name, 'xdebug') === 0
                || $name === 'apc.nmap_file_mask'
            ) {
                continue;
            }

            if (!isset($iniConfig[$name]) || $iniConfig[$name] !== $value) {
                //échappement des double guillemet à chaque valeur
                $content .= sprintf('%s="%s"%s', $name, addcslashes($value, '\\"'), PHP_EOL);
            }
        }

        return $content;
    }

    /**
     * Renvoie true si le nom du script peut être utilisé
     *
     * @return bool
     */
    private function checkMainScript(): bool
    {
        if (isset($this->script)) {
            return file_exists($this->script) || '--' === $this->script;
        }

        if (file_exists($this->script = $_SERVER['argv'][0])) {
            return true;
        }

        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
        $main = end($trace);

        if ($main !== false && isset($main['file'])) {
            return file_exists($this->script = $main['file']);
        }

        return false;
    }

    /**
     * Ajoute des paramètres de redémarrage à l'environnement
     *
     * @param string[] $envArgs
     *
     * @return void
     */
    private function setEnvRestartSettings(array $envArgs): void
    {
        $settings = [
            php_ini_loaded_file(),
            $envArgs[2],
            $envArgs[3],
            $envArgs[4],
            getenv($this->envOriginalIni),
            isset(self::$skipped) ? self::$skipped : null
        ];

        Process::setEnv(self::RESTART_SETTINGS, implode('|', $settings));
    }

    /**
     * Synchronise les paramètres et l'environnement s'il est appelé avec les paramètres existants
     *
     * @param restartData $settings
     *
     * @return void
     */
    private function syncSettings(array $settings): void
    {
        var_dump([
            '$settings' => $settings
        ]);
        trigger_error('Revenir à la fonction syncSettings()');
    }

    /**
     * Renvoie vrai s'il n'y a pas de problèmes de configuration connus
     *
     * @param string|null $info
     *
     * @return bool
     */
    private function checkConfiguration(?string &$info): bool
    {
        if (!function_exists('proc_open')) {
            $info = 'La fonction proc_open est désactivé.';
            return false;
        }

        if (extension_loaded('uopz')) {
            trigger_error('Revenir à la fonction checkConfiguration()');
            return false;
        }

        if (defined('PHP_WINDOWS_VERSION_BUILD') && PHP_VERSION_ID < 70400) {
            trigger_error('Revenir à la fonction checkConfiguration()');
        }

        return true;
    }

    /**
     * Active les signaux asynchrones et contrôle les interruptions dans le processus redémarré
     *
     * Disponible sur Unix PHP 7.1+ avec l'extension pcntl et Windows PHP 7.4+.
     *
     * @return void
     */
    private function tryEnableSignals(): void
    {
        if (function_exists('pcntl_async_signals') && function_exists('pcntl_signal')) {
            pcntl_async_signals(true);
            $message = 'Signal Async activé.';

            if (!self::$inRestart) {
                //Redémarrage, donc ignorer SIGINT dans le parent
                pcntl_signal(SIGINT, SIG_IGN);
            } elseif (is_int(pcntl_signal_get_handler(SIGINT))) {
                //Redémarré, aucun gestionnaire défini, donc forcer l'action par défaut
                pcntl_signal(SIGINT, SIG_DFL);
            }
        }

        if (!self::$inRestart && function_exists('sapi_windows_set_ctrl_handler')) {
            //Redémarrage, donc définissez un gestionnaire pour ignorer les événements CTRL dans le parent.
            sapi_windows_set_ctrl_handler(function ($evt) { });
        }
    }
    /**
     * Définis les propriété statique, $xdebugActive, $xdebugMode, $xdebugVersion
     * @return void
     */
    private static function setXdebugDetail(): void
    {
        if (isset(self::$xdebugActive)) {
            return;
        }
        self::$xdebugActive = false;
        if (!extension_loaded('xdebug')) {
            return;
        }

        $version = phpversion('xdebug');
        self::$xdebugVersion = $version !== false ? $version : 'unknown';

        if (version_compare(self::$xdebugVersion, '3.1', '>=')) {
            $modes = xdebug_info('mode');
            self::$xdebugMode = count($modes) === 0 ? 'off' : implode(',', $modes);
            self::$xdebugActive = self::$xdebugMode !== 'off';
            return;
        }

        //Voir si xdebug.mode est supporté dans cette version.
        $iniMode = ini_get('xdebug.mode');
        if ($iniMode === false) {
            self::$xdebugActive = true;
            return;
        }

        //la valeur d'environnement gagne mais ne peut pas être vide
        $envMode = (string) getenv('XDEBUG_MODE');
        if ($envMode !== '')
            self::$xdebugMode = $envMode;
        else
            self::$xdebugMode = ($iniMode !== '') ? $iniMode : 'off';

        //Une liste vide séparée par des virgules est traitée comme mode 'off'

        /** @var non-empty-string $replaceXdebugMode */
        $replaceXdebugMode = str_replace(' ', '', self::$xdebugMode);
        if (Preg::isMatch('{^,+$}', $replaceXdebugMode)) {
            self::$xdebugMode = 'off';
        }
        self::$xdebugActive = self::$xdebugMode !== 'off';
    }
}