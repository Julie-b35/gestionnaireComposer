<?php declare(strict_types=1);

/*
 * Ce fichier fait partie du projet: composer/pcre.
 *
 * (c) Composer <https://github.com/composer>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */
namespace Composer\Pcre\PregTests;

use Composer\Pcre\BaseTestCase;
use Composer\Pcre\Preg;

class MatchWithOffsetsTest extends BaseTestCase
{
    public function setUp(): void
    {
        $this->pregFunction = 'preg_match()';
    }

    public function testSuccess(): void
    {
        $count = Preg::matchWithOffsets('{(?P<m>[io])}', 'abcdefghijklmnopqrstuvwxyz', $matches);
        self::assertSame(1, $count);
        self::assertSame(array(0 => array('i', 8), 'm' => array('i', 8), 1 => array('i', 8)), $matches);
    }

    public function testFailure(): void
    {
        $count = Preg::matchWithOffsets('{abc}', 'def', $matches);
        self::assertSame(0, $count);
        self::assertSame([], $matches);
    }
    public function testBadPatternThrowIfWarningsAreNotThrowing(): void
    {
        $this->expectPcreException($pattern = '{(?P<m>[io])');
        @Preg::matchWithOffsets($pattern, 'abcdefghijklmnopqrstuvwxyz');
    }

    public function testBadPatternTriggerWarningByDefault(): void
    {
        $this->expectPcreWarning();
        Preg::matchWithOffsets('{(?P<m>[io])', 'abcdefghijklmnopqrstuvwxyz');
    }
}