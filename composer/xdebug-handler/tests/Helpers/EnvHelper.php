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

/**
 * Cette classe Helper fournit un fournisseur de données central qui utilise IniHelper pour simuler les paramètres d'environnement.
 * 
 * @phpstan-type envTestData array<string, array{0: string, 1: false|string, 2: false|string}>
 */
class EnvHelper
{
    /**
     * Simule l'environnement
     * @param string $iniFunc Méthode IniHelper à utiliser.
     * @param string|false $scanDir Valeur initial pour PHP_INI_SCAN_DIR
     * @param string|false $phprc Valeur initial pour PHPRC
     * @return IniHelper
     */
    public static function setInis(string $iniFunc, string|false $scanDir, string|false $phprc): IniHelper
    {
        $ini = new IniHelper([$scanDir, $phprc]);
        BaseTestCase::safeCall($ini, $iniFunc);

        return $ini;
    }

    /**
     * @return envTestData
     */
    public static function dataProvider(): array
    {
        $ini = new IniHelper();
        $loaded = $ini->getLoadedIni();
        $scanDir = $ini->getScanDir();
        return [
            'loaded false myIni' => ['setLoadedIni', false, '/my.ini'],
            'loaded empty false' => ['setLoadedIni', '', false],
            'scanned false file' => ['setScannedInis', false, $loaded],
            'scanned dir false' => ['setScannedInis', $scanDir, false]
        ];
    }
}