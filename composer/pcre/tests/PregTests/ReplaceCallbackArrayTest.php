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

class ReplaceCallbackArrayTest extends BaseTestCase
{
    public function setUp(): void
    {
        $this->pregFunction = 'preg_replace_callback_array()';
    }

    public function testSuccess(): void
    {
        $result = Preg::replaceCallbackArray(
            array(
                '{(?P<m>d)}' => function ($match) {
                    return '(' . $match[0] . ')';
                }
            ),
            'abcd',
            -1,
            $count
        );

        self::assertSame(1, $count);
        self::assertSame('abc(d)', $result);
    }

    public function testSuccessNoRef(): void
    {
        $result = Preg::replaceCallbackArray(
            array(
                '{(?P<m>d)}' => function ($match) {
                    return '(' . $match[0] . ')';
                }
            ),
            'abcd'
        );

        self::assertSame('abc(d)', $result);
    }

    public function testFailure(): void
    {
        $result = Preg::replaceCallbackArray(
            array(
                '{abc}' => function ($match) {
                    return '(' . $match[0] . ')';
                }
            ),
            'def',
            -1,
            $count
        );

        self::assertSame(0, $count);
        self::assertSame('def', $result);
    }

    public function testBadPatternThrowsIfWarningsAreNotThrowing(): void
    {
        $this->expectPcreException($pattern = '{(?P<m>d)');

        @Preg::replaceCallbackArray(
            array(
                $pattern => function ($match) {
                    return '(' . $match[0] . ')';
                }
            ),
            'abcd'
        );
    }

    public function testBadPatternTriggersWarningByDefault(): void
    {
        $this->expectPcreWarning();

        Preg::replaceCallbackArray(
            array(
                '{(?P<m>d)' => function ($match) {
                    return '(' . $match[0] . ')';
                }
            ),
            'abcd'
        );
    }
}