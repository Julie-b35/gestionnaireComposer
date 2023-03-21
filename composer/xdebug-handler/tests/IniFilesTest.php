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
use Composer\XdebugHandler\Tests\Helpers\IniHelper;
use Composer\XdebugHandler\Tests\Mocks\CoreMock;

class IniFilesTest extends BaseTestCase
{
    /**
     * Vérifie que les fichiers ini stockés dans la variable d'environnement _ORIGINAL_INIS 
     * sont formatés et signalés correctement.
     * @dataProvider iniFilesProvider
     * @return void
     */
    public function testGetAllIniFiles(string $iniFunc): void
    {
        $ini = new IniHelper();
        BaseTestCase::safeCall($ini, $iniFunc, null, $this);

        $loaded = true;
        $xdebug = CoreMock::createAndCheck($loaded);

        $this->checkRestart($xdebug);
        self::assertSame($ini->getIniFiles(), CoreMock::getAllIniFiles());
    }
    /**
     * @return array<string, string[]>
     */
    public function iniFilesProvider(): array
    {
        //$iniFunc
        return [
            'no-inis' => ['setNoInis'],
            'loaded-ini' => ['setLoadedIni'],
            'scanned-ini' => ['setScannedInis'],
            'all-ini' => ['setAllInis'],
        ];
    }

    /**
     * Summary of testTmpIni
     * @param string $iniFunc
     * @param int $matchCount
     * @return void
     */
    public function testTmpIni(string $iniFunc, int $matchCount): void
    {
        trigger_error('Revenir à la fonction testTmpIni()');
    }
    /**
     * @return array<string, string[]>
     */
    public function mergeIniProvider(): array
    {
        return [
            'simple' => ['date.timezone', 'Antarctica/McMurdo'],
            'single-quotes' => ['error_append_string', "<'color'>"],
            'newline' => ['error_append_string', "<color\n>"],
            'double-quotes' => ['error_append_string', '<style="color">'],
            'backslashes' => ['error_append_string', '<style=\\\\\\"color\\\\">'],
        ];
    }
}