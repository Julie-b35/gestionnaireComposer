<?php declare(strict_types=1);

/*
 * Ce fichier fait partie du projet: composer/pcre.
 *
 * (c) Composer <https://github.com/composer>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */
namespace Composer\Pcre;

use Composer\Pcre\Exceptions\PcreException;
use PHPUnit\Framework\TestCase;

class BaseTestCase extends TestCase
{
    protected ?string $pregFunction = null;
    /**
     * @param class-string<\Throwable> $class
     */
    protected function doExpectException(string $class, ?string $message = null): void
    {
        $this->expectException($class);
        if (!is_null($message)) {
            $this->expectExceptionMessage($message);
        }
    }
    protected function doExceptWarning(string $message): void
    {
        $this->expectWarning();
        $this->expectWarningMessage($message);
    }
    protected function expectPcreEngineException(string $pattern): void
    {
        $error = PHP_VERSION_ID >= 80000 ? 'Backtrack limit exhausted' : 'PREG_BACKTRACK_LIMIT_ERROR';
        $this->expectPcreException($pattern, $error);
    }

    protected function expectPcreException(string $pattern, ?string $error = null): void
    {
        if (null === $this->pregFunction) {
            self::fail('Le nom de fonction preg est manquant');
        }

        if (is_null($error)) {
            //Only use a message if the error can be reliably determined
            if (PHP_VERSION_ID >= 80000) {
                $error = 'Internal error';
            } elseif (PHP_VERSION_ID >= 70201) {
                $error = 'PREG_INTERNAL_ERROR';
            }
        }

        if (!is_null($error)) {
            $message = sprintf('%s: échec exécution \'%s\': %s', $this->pregFunction, $pattern, $error);
        } else {
            $message = null;
        }
        $this->doExpectException('Composer\Pcre\Exceptions\PcreException', $message);
    }
    protected function expectPcreWarning(?string $warning = null): void
    {
        if (null === $this->pregFunction) {
            self::fail('Le nom de fonction preg est manquant');
        }

        $warning = $warning !== null ? $warning : "No ending matching delimiter '}' found";
        $message = sprintf('%s: %s', $this->pregFunction, $warning);
        $this->doExceptWarning($message);
    }
}