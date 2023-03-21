<?php declare(strict_types=1);
/*
 * Ce fichier fait partie du projet: composer/xdebug-handler.
 *
 * (c) Composer <https://github.com/composer>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */
namespace Composer\XdebugHandler\Tests;

use Composer\XdebugHandler\Tests\Helpers\BaseTestCase;
use Composer\XdebugHandler\Tests\Helpers\EnvHelper;
use Composer\XdebugHandler\Tests\Mocks\PartialMock;

/**
 * @phpstan-import-type envTestData from EnvHelper
 */
class EnvironmentTest extends BaseTestCase
{
    /**
     * Vérifie que la variable d'environnement _ALLOW_XDEBUG 
     * est correctement formatée pour être utilisée dans le processus redémarré.
     * 
     * @param string $iniFunc Méthode IniHelper à utiliser.
     * @param string|false $scanDir Valeur initial pour PHP_INI_SCAN_DIR
     * @param string|false $phprc Valeur initial pour PHPRC
     * 
     * @dataProvider envAllowBeforeRestart
     * @return void
     */
    public function testEnvAllowBeforeRestart(string $iniFunc, string|false $scanDir, string|false $phprc): void
    {
        $ini = EnvHelper::setInis($iniFunc, $scanDir, $phprc);
        $loaded = true;

        PartialMock::createAndCheck($loaded);

        $args = [
            PartialMock::RESTART_ID,
            PartialMock::TEST_VERSION,
            $ini->hasScannedInis() ? '1' : '0',
            false !== $scanDir ? $scanDir : '*',
            false !== $phprc ? $phprc : '*'
        ];

        $expected = implode('|', $args);
        self::assertSame($expected, getenv(PartialMock::ALLOW_XDEBUG));
    }

    /**
     * @return envTestData
     */
    public function envAllowBeforeRestart(): array
    {
        return EnvHelper::dataProvider();
    }

    /**
     * Vérifie que la variable d'environnement _ALLOW_XDEBUG 
     * est correctement formatée pour être utilisée dans le processus redémarré.
     * 
     * @param string $iniFunc Méthode IniHelper à utiliser.
     * @param string|false $scanDir Valeur initial pour PHP_INI_SCAN_DIR
     * @param string|false $phprc Valeur initial pour PHPRC
     * @param bool $standard Si c'est un redémarrage standard
     * 
     * @dataProvider environmentBeforeRestart
     * @return void
     */
    public function testEnvironmentBeforeRestart(string $iniFunc, string|false $scanDir, string|false $phprc, bool $standard): void
    {
        EnvHelper::setInis($iniFunc, $scanDir, $phprc);
        $loaded = true;

        $settings = $standard ? [] : ['setPersistant' => []];

        $xdebug = PartialMock::createAndCheck($loaded, null, $settings);

        if (!$standard) {
            $scanDir = '';
            $phprc = $xdebug->getTmpIni();
        }

        $strategy = $standard ? 'standard' : 'persistant';
        self::assertSame($scanDir, getenv('PHP_INI_SCAN_DIR'), $strategy . ' scanDir');
        self::assertSame($phprc, getenv('PHPRC'), $strategy . ' phprc');
    }

    /**
     * @return array<string, array{0: string, 1: false|string, 2: false|string, 3:bool}>
     */
    public function environmentBeforeRestart(): array
    {
        $data = EnvHelper::dataProvider();
        $result = [];

        foreach ($data as $test => $params) {
            $params[3] = true;
            $result[$test . ' standard'] = $params;
            $params[3] = false;
            $result[$test . ' persistant'] = $params;
        }
        return $result;
    }
}