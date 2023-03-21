<?php declare(strict_types=1);

/*
 * Ce fichier fait partie du projet: composer/xdebug-handler.
 *
 * (c) Composer <https://github.com/composer>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */


namespace Composer\XdebugHandler\Tests\Mocks;

use Composer\XdebugHandler\Tests\Helpers\BaseTestCase;
use Composer\XdebugHandler\XdebugHandler;

/**
 * CoreMock fournit des fonctionnalités de base pour simuler la classe de XdebugHandler, 
 * en fournissant sa propre méthode de redémarrage qui simule un redémarrage
 * en créant une nouvelle instance de lui-même et en définissant la propriété CoreMock::restarted sur true, 
 * et une méthode publique getProperty qui accède aux propriétés privées. 
 * Étendez cette classe pour fournir des fonctionnalités supplémentaires.
 *
 * Peu importe que Xdebug soit chargé, car les valeurs de test écrasent les valeurs d'exécution dans le constructeur.
 *
 * Le fichier tmpIni est supprimé dans le destructeur.
 */
class CoreMock extends XdebugHandler
{
    const ALLOW_XDEBUG = 'MOCK_ALLOW_XDEBUG';

    const ORIGINAL_INIS = 'MOCK_ORIGINAL_INIS';

    const TEST_VERSION = '2.5.0';

    /**
     * @var bool
     */
    public bool $restarted;

    /**
     * @var string|null
     */
    public ?string $parentXdebugVersion;

    /**
     * @var static|null
     */
    protected ?CoreMock $childProcess;

    /**
     * @var \ReflectionClass<XdebugHandler>
     */
    protected \ReflectionClass $refClass;

    /**
     * @var array<string, mixed[]>
     */
    protected static array $settings;
    /**
     * @param bool|array{0: bool, 1:string} $loaded
     * @param static|null                   $parentProcess
     * @param array<string, mixed[]>        $settings
     * 
     * @return static
     */
    public static function createAndCheck(bool|array $loaded, ?self $parentProcess = null, array $settings = []): self
    {
        $mode = null;

        if (is_array($loaded))
            list($loaded, $mode) = $loaded;

        if (!is_null($mode) && !$loaded)
            throw new \InvalidArgumentException("Mode inattendue avec loaded à false: " . $mode . '.');

        $xdebug = new static($loaded, $mode);

        if (!is_null($parentProcess)) {
            //Ceci est un redémarrage, nous devons donc définir des propriétés de test spécifiques sur le parent et l'enfant
            $parentProcess->restarted = true;
            $xdebug->restarted = true;
            $xdebug->parentXdebugVersion = $parentProcess->parentXdebugVersion;

            //Rendre l'enfant disponible
            $parentProcess->childProcess = $xdebug;

            //Assurez-vous que $_SERVER a les changements d'environnement de redémarrage
            self::updateServerEnvironment();
        }

        foreach ($settings as $method => $args) {
            BaseTestCase::safeCall($xdebug, $method, $args);
        }

        static::$settings = $settings;

        $xdebug->check();
        return isset($xdebug->childProcess) ? $xdebug->childProcess : $xdebug;
    }

    final public function __construct(bool $loaded, ?string $mode)
    {
        parent::__construct('mock');

        $this->refClass = new \ReflectionClass('Composer\XdebugHandler\XdebugHandler');
        $this->parentXdebugVersion = $loaded ? static::TEST_VERSION : null;

        //Définis la propriété private static xdebugVersion
        $prop = $this->refClass->getProperty('xdebugVersion');
        $prop->setAccessible(true);
        $prop->setValue($this, $this->parentXdebugVersion);

        //Définis la propriété private static xdebugMode
        $prop = $this->refClass->getProperty('xdebugMode');
        $prop->setAccessible(true);
        $prop->setValue($this, $mode);

        //Définis la propriété private static xdebugActive
        $prop = $this->refClass->getProperty('xdebugActive');
        $prop->setAccessible(true);
        $prop->setValue($this, $loaded && $mode !== 'off');

        //Assurez-vous que la propriété skipped soit mise à null
        $prop = $this->refClass->getProperty('skipped');
        $prop->setAccessible(true);
        $prop->setValue($this, null);

        $this->restarted = false;
    }

    public function __destruct()
    {
        if (isset($this->tmpIni)) {
            @unlink($this->tmpIni);
        }
    }

    public function getProperty(string $name): mixed
    {
        $prop = $this->refClass->getProperty($name);
        $prop->setAccessible(true);
        return $prop->getValue($this);
    }
    /**
     * @param string[] $command
     * @return  void
     */
    protected function restart(array $command): void //@phpstan-ignore-line
    {
        static::createAndCheck(false, $this, static::$settings);
        return;
    }
    private static function updateServerEnvironment(): void
    {
        $names = [
            CoreMock::ALLOW_XDEBUG,
            CoreMock::ORIGINAL_INIS,
            'PHP_INI_SCAN_DIR',
            'PHPRC',
        ];

        foreach ($names as $name) {
            $value = getenv($name);
            if (false === $value) {
                unset($_SERVER[$name]);
            } else {
                $_SERVER[$name] = $value;
            }
        }
    }
}