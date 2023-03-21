<?php declare(strict_types=1);

/*
 * Ce fichier fait partie du projet: composer/xdebug-handler.
 *
 * (c) Composer <https://github.com/composer>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace Composer\XdebugHandler;

use Composer\Pcre\Preg;

/**
 * fonction utilitaire pour des processus.
 * @author John Stevenson <john-stevenson@blueyonder.co.uk>
 */
class Process
{
    /**
     * Échappe une chaîne à utiliser comme argument de shell.
     * 
     * From https://github.com/johnstevenson/winbox-args
     * MIT Licensed (c) John Stevenson <john-stevenson@blueyonder.co.uk>
     * 
     * @param string $arg   L'argument à échapper
     * @param bool $meta    Échappement additionnel des meta caractère pour cmd.exe
     * @param bool $module  L'argument qui est le module à invoqué
     * 
     * @return string
     */
    public static function escape(string $arg, bool $meta = true, bool $module = false): string
    {
        if (!defined('PHP_WINDOWS_VERSION_BUILD')) {
            return sprintf("'%s'", str_replace("'", "'\\''", $arg));
        }

        $quote = strpbrk($arg, " \t") !== false || $arg === '';

        $arg = Preg::replace('/(\\\\*)"/', '$1$1\\"', $arg, -1, $countQuotes);
        if (!is_string($arg))
            throw new \Exception('Preg::Replace doit renvoyez une chaînes.');

        if ($meta) {
            /** @var non-empty-string $arg */
            $meta = (bool) $countQuotes || Preg::isMatch('/%[^%]+%/', $arg);

            if ($meta) {
                $quote = $quote || strpbrk($arg, '^&|<>()') !== false;
            } elseif ($module && !(bool) $countQuotes && $quote) {
                $meta = false;
            }
        }

        if ($quote) {
            $replaceArg = Preg::replace('/(\\\\*)$/', '$1$1', $arg);
            if (!is_string($replaceArg))
                throw new \Exception('Preg::Replace doit renvoyez une chaînes.');
            $arg = '"' . $replaceArg . '"';
        }

        if ($meta) {
            $arg = Preg::replace('/(["^&|<>()%])/', '^$1', $arg);
            if (!is_string($arg))
                throw new \Exception('Preg::Replace doit renvoyez une chaînes.');
        }
        return $arg;
    }
    /**
     * Échappe un tableau d'arguments qui composent une commande shell
     * 
     * @param string[] $args
     * @return string
     */
    public static function escapeShellCommand(array $args): string
    {
        $command = '';
        $module = array_shift($args);

        if (isset($module)) {
            $command = self::escape($module, true, true);

            foreach ($args as $arg) {
                $command .= ' ' . self::escape($arg);
            }
        }

        return $command;
    }
    /**
     * Rend les changements d'environnement putenv disponibles dans $_SERVER et $_ENV
     * 
     * @param string $name Nom de la variable d'environnement
     * @param string|null $value Valeur de la variable d'environnement (null supprime cette variable.)
     * 
     * @return bool
     */
    public static function setEnv(string $name, ?string $value = null): bool
    {
        $unset = is_null($value);

        if (!putenv($unset ? $name : $name . '=' . $value)) {
            return false;
        }

        if ($unset) {
            unset($_SERVER[$name]);
        } else {
            $_SERVER[$name] = $value;
        }

        if (false !== stripos((string) ini_get('variables_order'), 'E')) {
            if ($unset)
                unset($_ENV[$name]);
            else
                $_ENV[$name] = $value;
        }

        return true;
    }
}