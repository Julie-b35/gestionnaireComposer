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

use Composer\Pcre\Exceptions\UnexpectedNullMatchException;
use Composer\Pcre\BaseTestCase;
use Composer\Pcre\Preg;

class IsMatchTest extends BaseTestCase
{
    public function setUp(): void
    {
        $this->pregFunction = 'preg_match()';
    }

    public function testSuccess(): void
    {
        $result = Preg::isMatch('{(?P<m>[io])}', 'abcdefghijklmnopqrstuvwxyz', $matches);
        self::assertSame(true, $result);
        self::assertSame(array(0 => 'i', 'm' => 'i', 1 => 'i'), $matches);
    }

    public function testSuccessNoRef(): void
    {
        $result = Preg::isMatch('{(?P<m>[io])}', 'abcdefghijklmnopqrstuvwxyz');
        self::assertSame(true, $result);
    }

    public function testFailure(): void
    {
        $result = Preg::isMatch('{abc}', 'def', $matches);
        self::assertSame(false, $result);
        self::assertSame(array(), $matches);
    }
    public function testSuccessStrictGroups(): void
    {
        $result = Preg::isMatchStrictGroups('{(?P<m>\d)(?<matched>a)?}', '3a', $matches);
        self::assertSame(true, $result);
        self::assertSame([0 => "3a", "m" => "3", 1 => "3", 'matched' => 'a', 2 => 'a'], $matches);
    }

    public function testFailStrictGroup(): void
    {
        self::expectException(UnexpectedNullMatchException::class);
        self::expectExceptionMessage("Le pattern '{(?P<m>\d)(?<unmatched>a)?}' n'avais pas de correspondance qui est attendu pour le groupe : unmatched, assurez-vous que le pattern à toujours des correspondances ou utiliser . 'match' à la place.");
        Preg::isMatchStrictGroups('{(?P<m>\d)(?<unmatched>a)?}', '123', $matches);
    }
    public function testBadPatternThrowsIfWarningsAreNotThrowing(): void
    {
        $this->expectPcreException($pattern = '{(?P<m>[io])');
        @Preg::isMatch($pattern, 'abcdefghijklmnopqrstuvwxyz');
    }

    public function testBadPatternTriggersWarningByDefault(): void
    {
        $this->expectPcreWarning();
        Preg::isMatch('{(?P<m>[io])', 'abcdefghijklmnopqrstuvwxyz');
    }
}