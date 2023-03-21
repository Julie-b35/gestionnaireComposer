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

use PHPUnit\Framework\TestCase;
use Composer\XdebugHandler\Tests\Mocks\CoreMock;
use Composer\XdebugHandler\Tests\Mocks\FailMock;
use Composer\XdebugHandler\XdebugHandler;

abstract class BaseTestCase extends TestCase
{
    /**
     * @var array<string, string|false>
     */
    private static $env = [];

    /** @var string[] */
    private static $argv = [];

    /** @var string[] */
    private static $names = [
        CoreMock::ALLOW_XDEBUG,
        CoreMock::ORIGINAL_INIS,
        'PHP_INI_SCAN_DIR',
        'PHPRC',
        XdebugHandler::RESTART_SETTINGS,
    ];
    /**
     * Annule les variables d'environnement pour chaque test et restaure argv
     * Appelé depuis PHPUnit
     */
    protected function setUp(): void
    {
        foreach (self::$names as $name) {
            putenv($name);
            unset($_SERVER[$name]);
        }

        $_SERVER['argv'] = self::$argv;
    }

    /**
     * Enregistre l'environnement actuel et l'état argv
     * Appelé depuis PHPUnit
     */
    public static function setUpBeforeClass(): void
    {
        foreach (self::$names as $name) {
            self::$env[$name] = getenv($name);
            // Note $_SERVER will already match
        }

        self::$argv = $_SERVER['argv'];
    }

    /**
     * Restaure l'environnement d'origine et l'état argv
     * Appelé depuis PHPUnit
     */
    public static function tearDownAfterClass(): void
    {
        foreach (self::$env as $name => $value) {
            if (false !== $value) {
                putenv($name . '=' . $value);
                $_SERVER[$name] = $value;
            } else {
                putenv($name);
                unset($_SERVER[$name]);
            }
        }

        $_SERVER['argv'] = self::$argv;
    }
    /**
     * @param mixed $instance
     * @param string $method
     * @param mixed[] $params
     * @param self|null $self
     * @return mixed
     */
    public static function safeCall($instance, string $method, ?array $params = null, ?self $self = null): mixed
    {
        $callable = [$instance, $method];
        $params = !is_null($params) ? $params : [];

        if (is_callable($callable))
            return call_user_func_array($callable, $params);

        $msgError = "Impossible d'appeler la méthode : " . $method;
        if (!is_null($self))
            self::fail($msgError);

        throw new \LogicException($msgError);
    }

    /**
     * Fournit des assertions de base pour un processus redémarré
     *
     * @param \Composer\XdebugHandler\Tests\Mocks\CoreMock $xdebug
     *
     * @return void
     */
    protected function checkRestart(CoreMock $xdebug): void
    {
        //Le processus à dû être redémarrés
        self::assertTrue($xdebug->restarted);

        //Env ALLOW_XDEBUG doit être désactivé
        self::assertFalse(getenv(CoreMock::ALLOW_XDEBUG));
        self::assertArrayNotHasKey(CoreMock::ALLOW_XDEBUG, $_SERVER);

        //Env ORIGINAL_INIS doit être défini et être une chaîne
        self::assertIsString(getenv(CoreMock::ORIGINAL_INIS));
        self::assertArrayHasKey(CoreMock::ORIGINAL_INIS, $_SERVER);

        //La version ignorée ne doit être signalée que si elle a été déchargée lors du redémarrage
        if ($xdebug->parentXdebugVersion === null) {
            //Redémarrage réussi simulé sans Xdebug
            $version = '';
        } elseif ($xdebug instanceof FailMock) {
            //Échec du redémarrage simulé, avec Xdebug toujours chargé
            $version = '';
        } else {
            $version = CoreMock::TEST_VERSION;
        }

        self::assertSame($version, $xdebug::getSkippedVersion());

        //Env RESTART_SETTINGS doit être défini et être une chaîne
        self::assertIsString(getenv(CoreMock::RESTART_SETTINGS));
        self::assertArrayHasKey(CoreMock::RESTART_SETTINGS, $_SERVER);

        //Les paramètres de redémarrage doivent être un tableau
        self::assertTrue(is_array($xdebug::getRestartSettings()));
    }
}