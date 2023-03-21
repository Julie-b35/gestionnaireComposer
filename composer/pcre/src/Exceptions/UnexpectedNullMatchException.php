<?php declare(strict_types=1);

/*
 * Ce fichier fait partie du projet: composer/pcre.
 *
 * (c) Composer <https://github.com/composer>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */
namespace Composer\Pcre\Exceptions;

class UnexpectedNullMatchException extends PcreException
{
    public static function fromFunction($function, $pattern): self
    {
        throw new \LogicException("fromFunction ne doit pas être appelé depuis la classe '" . self::class . "' utiliser '" . PcreException::class . "'.");
    }
}