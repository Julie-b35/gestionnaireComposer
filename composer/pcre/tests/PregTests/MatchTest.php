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
use Composer\Pcre\Exceptions\UnexpectedNullMatchException;
use Composer\Pcre\Preg;

class MatchTest extends BaseTestCase
{
    public function setUp(): void
    {
        $this->pregFunction = 'preg_match()';
    }

    public function testSuccess(): void
    {
        $count = Preg::match('{(?P<m>[io])}', 'abcdefghijklmnopqrstuvwxyz', $matches);
        self::assertSame(1, $count);
        self::assertSame([0 => "i", "m" => "i", 1 => "i"], $matches);
    }

    public function testSuccessWithInt(): void
    {
        $count = Preg::match('{(?P<m>\d)}', (string) 123, $matches);
        self::assertSame(1, $count);
        self::assertSame([0 => "1", "m" => "1", 1 => "1"], $matches);
    }

    public function testSuccessStrictGroups(): void
    {
        $count = Preg::matchStrictGroups('{(?P<m>\d)(?<matched>a)?}', '3a', $matches);
        self::assertSame(1, $count);
        self::assertSame([0 => "3a", "m" => "3", 1 => "3", 'matched' => 'a', 2 => 'a'], $matches);
    }

    public function testFailStrictGroup(): void
    {
        self::expectException(UnexpectedNullMatchException::class);
        self::expectExceptionMessage("Le pattern '{(?P<m>\d)(?<unmatched>a)?}' n'avais pas de correspondance qui est attendu pour le groupe : unmatched, assurez-vous que le pattern à toujours des correspondances ou utiliser . 'match' à la place.");
        Preg::matchStrictGroups('{(?P<m>\d)(?<unmatched>a)?}', '123', $matches);
    }

    /**
     * @SuppressWarnings(PHP0406)
     * @return void
     */
    public function testTypeErrorWithNull(): void
    {
        $this->expectException('TypeError');
        $count = Preg::match('{(?P<m>\d)}', null, $matches); // @phpstan-ignore-line
    }

    public function testSuccessNoRef(): void
    {
        $count = Preg::match('{(?P<m>[io])}', 'abcdefghijklmnopqrstuvwxyz');
        self::assertSame(1, $count);
    }

    public function testFailure(): void
    {
        $count = Preg::match('{abc}', 'def', $matches);
        self::assertSame(0, $count);
        self::assertSame([], $matches);
    }

    public function testBadPatternThrowIfWarningsAreNotThrowing(): void
    {
        $this->expectPcreException($pattern = '{(?P<m>[io])');
        @Preg::match($pattern, 'abcdefghijklmnopqrstuvwxyz');
    }

    public function testBadPatternTriggerWarningByDefault(): void
    {
        $this->expectPcreWarning();
        Preg::match('{(?P<m>[io])', 'abcdefghijklmnopqrstuvwxyz');
    }

    public function testThrowIfEngineErrors(): void
    {
        $this->expectPcreEngineException($pattern = '/(?:\D+|<\d+>)*[!?]/');
        Preg::match($pattern, 'foobar foobar foobar');
    }
}