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

class iSMatchAllTest extends BaseTestCase
{
    public function setUp(): void
    {
        $this->pregFunction = 'preg_match_all()';
    }

    public function testSuccess(): void
    {
        $result = Preg::isMatchAll('{[aei]}', 'abcdefghijklmnopqrstuvwxyz', $matches);
        self::assertSame(true, $result);
        self::assertSame([0 => ['a', 'e', 'i']], $matches);
    }

    public function testSuccessNoRef(): void
    {
        $result = Preg::isMatchAll('{[aei]}', 'abcdefghijklmnopqrstuvwxyz');
        self::assertSame(true, $result);
    }

    public function testFailure(): void
    {
        $result = Preg::isMatchAll('{[abc]}', 'def', $matches);
        self::assertSame(false, $result);
        self::assertSame([[]], $matches);
    }

    public function testSuccessStrictGroups(): void
    {
        $result = Preg::isMatchAllStrictGroup('{(?P<m>\d)(?<matched>a)?}', '3a', $matches);
        self::assertSame(true, $result);
        self::assertSame([0 => ['3a'], 'm' => ['3'], 1 => ['3'], 'matched' => ['a'], 2 => ['a']], $matches);
    }

    public function testFaiStrictGroup(): void
    {
        self::expectException(UnexpectedNullMatchException::class);
        self::expectExceptionMessage("Le pattern '{(?P<m>\d)(?<unmatched>a)?}' n'avais pas de correspondance qui est attendu pour le groupe : unmatched, assurez-vous que le pattern à toujours des correspondances ou utiliser . 'matchAll' à la place.");
        Preg::isMatchAllStrictGroup('{(?P<m>\d)(?<unmatched>a)?}', '123', $matches);
    }

    public function testBadPatternThrowIfWarningsAreNotThrowing(): void
    {
        $this->expectPcreException($pattern = '{[aei]');
        @Preg::isMatchAll($pattern, 'abcdefghijklmnopqrstuvwxyz');
    }

    public function testBadPatternTriggersWarningByDefault(): void
    {
        $this->expectPcreWarning();
        Preg::isMatchAll('{[aei]', 'abcdefghijklmnopqrstuvwxyz');
    }
}