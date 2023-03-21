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

use Composer\XdebugHandler\Tests\Helpers\Logger;
use Composer\XdebugHandler\XdebugHandler;
use PHPUnit\Framework\TestCase;

class ClassTest extends TestCase
{
    public function testConstructorThrowsOnEmptyEnvPrefix(): void
    {
        $this->expectException('RuntimeException');
        new XdebugHandler('');
    }

    public function testSetterAreFluent(): void
    {
        $xdebug = new XdebugHandler('my-appli');
        //$logger = new Logger();
        self::assertInstanceOf(get_class($xdebug), $xdebug, 'XdebugHandler');

        $result = $xdebug->setLogger(new Logger());
        self::assertInstanceOf(get_class($xdebug), $result, 'setLogger');

        $result = $xdebug->setMainScript('--');
        self::assertInstanceOf(get_class($xdebug), $result, 'setMainScript');

        $result = $xdebug->setPersistant();
        self::assertInstanceOf(get_class($xdebug), $result, 'setPersistant');
    }
}