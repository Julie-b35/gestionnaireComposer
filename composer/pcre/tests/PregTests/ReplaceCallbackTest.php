<?php

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

class ReplaceCallbackTest extends BaseTestCase
{
    public function setUp(): void
    {
        $this->pregFunction = 'preg_replace_callback()';
    }

    public function testSuccess(): void
    {
        $result = Preg::replaceCallback('{(?P<m>d)}', function ($match) {
            return '(' . $match[0] . ')';
        }, 'abcd', -1, $count);

        self::assertSame(1, $count);
        self::assertSame('abc(d)', $result);
    }

    public function testSuccessWithOffset(): void
    {
        $result = Preg::replaceCallback('{(?P<m>d)}', function ($match) {
            /** @var array<int|string, list<string|int>>  $match */
            return '(' . $match[0][0] . ')';
        }, 'abcd', -1, $count, PREG_OFFSET_CAPTURE);

        self::assertSame(1, $count);
        self::assertSame('abc(d)', $result);
    }

    public function testSuccessNoRef(): void
    {
        $result = Preg::replaceCallback('{(?P<m>d)}', function ($match) {
            return '(' . $match[0] . ')';
        }, 'abcd');

        self::assertSame('abc(d)', $result);
    }

    public function testFailure(): void
    {
        $result = Preg::replaceCallback('{abc}', function ($match) {
            return '(' . $match[0] . ')';
        }, 'def', -1, $count);

        self::assertSame(0, $count);
        self::assertSame('def', $result);
    }

    public function testSuccessStrictGroups(): void
    {
        $result = Preg::replaceCallbackStrictGroups('{(?P<m>\d)(?<matched>a)?}', function ($match) {
            return strtoupper($match['matched']);
        }, '3a', -1, $count);

        self::assertSame(1, $count);
        self::assertSame('A', $result);
    }

    public function testFailStrictGroups(): void
    {
        self::expectException(UnexpectedNullMatchException::class);
        self::expectExceptionMessage("Le pattern '{(?P<m>\d)(?<unmatched>a)?}' n'avais pas de correspondance qui est attendu pour le groupe : unmatched, assurez-vous que le pattern à toujours des correspondances ou utiliser . 'replaceCallback' à la place.");

        $result = Preg::replaceCallbackStrictGroups('{(?P<m>\d)(?<unmatched>a)?}', function ($match) {
            return strtoupper($match['unmatched']);
        }, '123', -1, $count);
    }

    public function testBadPatternThrowsIfWarningsAreNotThrowing(): void
    {
        $this->expectPcreException($pattern = '{(?P<m>d)');

        @Preg::replaceCallback($pattern, function ($match) {
            return '(' . $match[0] . ')';
        }, 'abcd');
    }

    public function testBadPatternTriggersWarningByDefault(): void
    {
        $this->expectPcreWarning();

        Preg::replaceCallback('{(?P<m>d)', function ($match) {
            return '(' . $match[0] . ')';
        }, 'abcd');
    }
}