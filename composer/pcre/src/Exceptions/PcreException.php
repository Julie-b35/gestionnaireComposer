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

class PcreException extends \RuntimeException
{
    /**
     * @param string $function
     * @param string|string[] $pattern
     * @return self
     */
    public static function fromFunction($function, $pattern): self
    {
        $code = preg_last_error();

        if (is_array($pattern)) {
            $pattern = implode(', ', $pattern);
        }
        return new PcreException($function . "(): échec exécution '" . $pattern . "': " . self::pcreLastErrorMessage(), $code);
    }

    private static function pcreLastErrorMessage(): string
    {
        if (function_exists('preg_last_error_msg')) {
            return preg_last_error_msg();
        }
        return 'ERREUR_INDÉFINIES';
    }
}