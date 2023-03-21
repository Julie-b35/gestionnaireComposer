<?php declare(strict_types=1);

/*
 * Ce fichier fait partie du projet: composer/xdebug-handler.
 *
 * (c) Composer <https://github.com/composer>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace Composer\XdebugHandler\Tests\Helpers;

use Composer\XdebugHandler\Tests\Mocks\CoreMock;

/**
 * Cette classe Helper nous permet de simuler les fichiers php ini 
 * que le processus signalerait autrement via php_ini_loaded_file et php_ini_scanned_files.
 */
class IniHelper
{
    /**
     * @var string
     */
    protected string $loadedIni;

    /**
     * @var string
     */
    protected string $scanDir;

    /**
     * @var string[]
     */
    protected array $files;

    /**
     * @var null|array{0: false|string, 1: false|string}
     */
    protected ?array $envOptions;

    /**
     * @param null|array{0: false|string, 1: false|string} $envOptions
     */
    public function __construct(?array $envOptions = null)
    {
        $this->envOptions = $envOptions;
        $base = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'Fixtures';
        $this->loadedIni = $base . DIRECTORY_SEPARATOR . 'php.ini';
        $this->scanDir = $base . DIRECTORY_SEPARATOR . 'scandir';
    }

    public function setNoInis(): void
    {
        $this->files = [''];
        $this->setEnvironment();
    }

    public function setLoadedIni(): void
    {
        $this->files = [
            $this->loadedIni,
        ];

        $this->setEnvironment();
    }

    public function setScannedInis(): void
    {
        $this->files = [
            '',
            $this->scanDir . DIRECTORY_SEPARATOR . 'scan-one.ini',
            $this->scanDir . DIRECTORY_SEPARATOR . 'scan-two.ini',
            $this->scanDir . DIRECTORY_SEPARATOR . 'scan-empty.ini',
        ];

        $this->setEnvironment();
    }

    public function setAllInis(): void
    {
        $this->files = [
            $this->loadedIni,
            $this->scanDir . DIRECTORY_SEPARATOR . 'scan-one.ini',
            $this->scanDir . DIRECTORY_SEPARATOR . 'scan-two.ini',
            $this->scanDir . DIRECTORY_SEPARATOR . 'scan-empty.ini',
        ];

        $this->setEnvironment();
    }

    public function setInaccessibleIni(): void
    {
        trigger_error('Revenir à la fonction setInaccessibleIni()');
    }

    public function setSectionInis(string $sectionName): void
    {
        trigger_error('Revenir à la fonction setSectionInis()');
    }

    /**
     * @return string[]
     */
    public function getIniFiles(): array
    {
        return isset($this->files) ? $this->files : [];
    }

    public function hasScannedInis(): bool
    {
        return isset($this->files) && count($this->files) > 1;
    }

    public function getLoadedIni(): string
    {
        return $this->loadedIni;
    }

    public function getScanDir(): string
    {
        return $this->scanDir;
    }

    private function setEnvironment(): void
    {
        //Définissez ORIGINAL_INIS. Les valeurs doivent être séparées par des chemins
        $this->setEnv(CoreMock::ORIGINAL_INIS, implode(PATH_SEPARATOR, $this->files));

        if (isset($this->envOptions)) {
            list($scandir, $phprc) = $this->envOptions;
            $this->setEnv('PHP_INI_SCAN_DIR', $scandir);
            $this->setEnv('PHPRC', $phprc);
        }
    }

    /**
     * @param string $name
     * @param string|false $value
     * @return void
     */
    private function setEnv(string $name, string|false $value): void
    {
        if (false !== $value) {
            putenv($name . '=' . $value);
            $_SERVER[$name] = $value;
        } else {
            putenv($name);
            unset($_SERVER[$name]);
        }
    }
}