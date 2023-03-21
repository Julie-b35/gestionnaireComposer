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

class IsMatchAllWithOffsetsTest extends BaseTestCase
{
    public function setUp(): void
    {
        $this->pregFunction = 'preg_match_all()';
    }

    public function testSuccess(): void
    {
        $status = Preg::isMatchAllWithOffsets('{[aei]}', 'abcdefghijklmnopqrstuvwxyz', $matches);
        self::assertSame(true, $status);
        self::assertSame([0 => [['a', 0], ['e', 4], ['i', 8]]], $matches);
    }

    public function testFailure(): void
    {
        $status = Preg::isMatchAllWithOffsets('{[abc]}', 'def', $matches);
        self::assertSame(false, $status);
        self::assertSame([[]], $matches);
    }
    public function testBadPatternThrowIfWarningsAreNotThrowing(): void
    {
        $this->expectPcreException($pattern = '{[aei]');
        @Preg::isMatchAllWithOffsets($pattern, 'abcdefghijklmnopqrstuvwxyz', $matches);
    }

    public function testBadPatternTriggersWarningByDefault(): void
    {
        $this->expectPcreWarning();
        Preg::isMatchAllWithOffsets('{[aei]', 'abcdefghijklmnopqrstuvwxyz', $matches);
    }
}