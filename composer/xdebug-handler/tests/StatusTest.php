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
use Composer\XdebugHandler\Tests\Helpers\Logger;
use Composer\XdebugHandler\Tests\Mocks\CoreMock;

class StatusTest extends BaseTestCase
{
    public function testSetLoggerProvidesOutput(): void
    {
        $loaded = true;

        $logger = new Logger();
        $settings = ['setLogger' => [$logger]];

        $xdebug = CoreMock::createAndCheck($loaded, null, $settings);
        $this->checkRestart($xdebug);

        $output = $logger->getOutput();
        self::assertNotEmpty($output);
    }
}