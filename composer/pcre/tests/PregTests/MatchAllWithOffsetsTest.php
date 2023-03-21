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
use Composer\Pcre\Exceptions\UnexpectedNullMatchException;

class MatchAllWithOffsetsTest extends BaseTestCase
{
    public function setUp(): void
    {
        $this->pregFunction = 'preg_match_all()';
    }

    public function testSuccess(): void
    {
        $count = Preg::matchAllWithOffsets('{[aei]}', 'abcdefghijklmnopqrstuvwxyz', $matches);
        self::assertSame(3, $count);
        self::assertSame([0 => [['a', 0], ['e', 4], ['i', 8]]], $matches);
    }

    public function testFailure(): void
    {
        $count = Preg::matchAllWithOffsets('{[abc]}', 'def', $matches);
        self::assertSame(0, $count);
        self::assertSame([[]], $matches);
    }
    public function testBadPatternThrowIfWarningsAreNotThrowing(): void
    {
        $this->expectPcreException($pattern = '{[aei]');
        @Preg::matchAllWithOffsets($pattern, 'abcdefghijklmnopqrstuvwxyz', $matches);
    }

    public function testBadPatternTriggersWarningByDefault(): void
    {
        $this->expectPcreWarning();
        Preg::matchAllWithOffsets('{[aei]', 'abcdefghijklmnopqrstuvwxyz', $matches);
    }
}