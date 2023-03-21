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
use Composer\Pcre\Exceptions\UnexpectedNullMatchException;

/**
 * TODO: Suppression d'un faux positif int-mask non géré par PHP de devsense le détectant comme chaîne de classe invalide.
 * @suppress PHP0413
 */
class Preg
{
    /**
     * @param non-empty-string $pattern
     * @param non-empty-string $subject
     * @param array<string|null> $matches
     * @param int-mask<PREG_UNMATCHED_AS_NULL|PREG_OFFSET_CAPTURE> $flags
     * @param int $offset
     * @return 0|1
     * 
     * @param-out array<int|string, string|null> $matches
     */
    public static function match(string $pattern, string $subject, ?array &$matches = null, int $flags = 0, int $offset = 0): int
    {
        self::checkOffsetCapture($flags, 'matchWithOffsets');

        $result = preg_match($pattern, $subject, $matches, $flags | PREG_UNMATCHED_AS_NULL, $offset);
        if ($result === false) {
            throw PcreException::fromFunction("preg_match", $pattern);
        }
        return $result;
    }

    /**
     * Une variante de la méthode `match()` qui renvoie des correspondances non null (ou une exception)
     * 
     * @param non-empty-string $pattern
     * @param non-empty-string $subject
     * @param array<string> $matches
     * @param int-mask<PREG_UNMATCHED_AS_NULL|PREG_OFFSET_CAPTURE> $flags
     * @param int $offset
     * @return 0|1
     * 
     * @param-out array<int|string, string> $matches
     */
    public static function matchStrictGroups(string $pattern, string $subject, ?array &$matches = null, int $flags = 0, int $offset = 0): int
    {
        $result = self::match($pattern, $subject, $matchesInternal, $flags, $offset);
        $matches = self::enforceNonNullMatches($pattern, $matchesInternal, 'match');

        return $result;
    }

    /**
     * Exécuter preg_match avec PREG_OFFSET_CAPTURE
     * 
     * @param non-empty-string $pattern
     * @param non-empty-string $subject
     * @param array<int|string, array{string|null, int}> $matches
     * @param int-mask<PREG_UNMATCHED_AS_NULL|PREG_OFFSET_CAPTURE> $flags
     * @param int $offset
     * @return 0|1
     * 
     * @param-out array<int|string, array{string|null, int<-1, max>}> $matches
     */
    public static function matchWithOffsets(string $pattern, string $subject, ?array &$matches = null, int $flags = 0, int $offset = 0): int
    {

        $result = preg_match($pattern, $subject, $matches, $flags | PREG_UNMATCHED_AS_NULL | PREG_OFFSET_CAPTURE, $offset);
        if ($result === false) {
            throw PcreException::fromFunction("preg_match", $pattern);
        }
        return $result;
    }

    /**
     * @param non-empty-string $pattern
     * @param non-empty-string $subject
     * @param array<int|string, list<string|null>> $matches définis par la méthode.
     * @param int-mask<PREG_PATTERN_ORDER|PREG_SET_ORDER|PREG_OFFSET_CAPTURE|PREG_UNMATCHED_AS_NULL> $flags
     * @param int $offset
     * @return 0|positive-int
     * 
     * @param-out array<int|string, list<string|null>> $matches
     */
    public static function matchAll(string $pattern, string $subject, ?array &$matches = null, int $flags = 0, int $offset = 0): int
    {
        self::checkOffsetCapture($flags, 'matchAllWithOffsets');
        self::checkSetOrder($flags);

        $result = preg_match_all($pattern, $subject, $matches, $flags | PREG_UNMATCHED_AS_NULL, $offset);
        if (!is_int($result)) {
            throw PcreException::fromFunction("preg_match_all", $pattern);
        }
        return $result;
    }

    /**
     * @param non-empty-string $pattern
     * @param non-empty-string $subject
     * @param array<int|string, list<string|null>> $matches définis par la méthode.
     * @param int-mask<PREG_PATTERN_ORDER|PREG_SET_ORDER|PREG_OFFSET_CAPTURE|PREG_UNMATCHED_AS_NULL> $flags
     * @param int $offset
     * @return 0|positive-int
     * 
     * @param-out array<int|string, list<string>> $matches
     */
    public static function matchAllStrictGroup(string $pattern, string $subject, ?array &$matches = null, int $flags = 0, int $offset = 0): int
    {
        $result = self::matchAll($pattern, $subject, $matchesInternal, $flags, $offset);
        $matches = self::enforceNonNullMatchAll($pattern, $matchesInternal, 'matchAll');
        return $result;
    }

    /**
     * Exécuter preg_match avec PREG_OFFSET_CAPTURE
     * 
     * @param non-empty-string $pattern
     * @param non-empty-string $subject
     * @param array<int|string, list<array{string|null, int}>> $matches définis par la méthode.
     * @param int-mask<PREG_PATTERN_ORDER|PREG_SET_ORDER|PREG_OFFSET_CAPTURE|PREG_UNMATCHED_AS_NULL> $flags
     * @param int $offset
     * @return 0|positive-int
     * 
     * @param-out array<int|string, list<array{string|null, int<-1, max>}>> $matches
     */
    public static function matchAllWithOffsets(string $pattern, string $subject, ?array &$matches, int $flags = 0, int $offset = 0): int
    {
        self::checkSetOrder($flags);

        $result = preg_match_all($pattern, $subject, $matches, $flags | PREG_UNMATCHED_AS_NULL | PREG_OFFSET_CAPTURE, $offset);
        if (!is_int($result)) {
            throw PcreException::fromFunction("preg_match_all", $pattern);
        }
        return $result;
    }
    /**
     * @param string|string[] $pattern
     * @param string|string[] $replacement
     * @param string $subject
     * @param int $limit
     * 
     * @return string|string[]
     * 
     * @param-out int<0, max> $count
     */
    public static function replace(array|string $pattern, array|string $replacement, string $subject, int $limit = -1, int &$count = null): array|string
    {
        $result = preg_replace($pattern, $replacement, $subject, $limit, $count);
        if (is_null($result)) {
            throw PcreException::fromFunction('preg_replace', $pattern);
        }
        return $result;
    }

    /**
     * @param string|string[] $pattern
     * @param callable(array<int|string, string|null>): string $replacement
     * @param string $subject
     * @param int             $count Set by method
     * @param int-mask<PREG_UNMATCHED_AS_NULL|PREG_OFFSET_CAPTURE> $flags PREG_OFFSET_CAPTURE is supported, PREG_UNMATCHED_AS_NULL is always set
     *
     * @param-out int<0, max> $count
     */
    public static function replaceCallback($pattern, callable $replacement, string $subject, int $limit = -1, int &$count = null, int $flags = 0): string
    {

        $result = preg_replace_callback($pattern, $replacement, $subject, $limit, $count, $flags | PREG_UNMATCHED_AS_NULL);
        if ($result === null) {
            throw PcreException::fromFunction('preg_replace_callback', $pattern);
        }

        return $result;
    }

    /**
     * Variant of `replaceCallback()` which outputs non-null matches (or throws)
     *
     * @param non-empty-string $pattern
     * @param callable(array<int|string, string>): string $replacement
     * @param string $subject
     * @param int $count Set by method
     * @param int-mask<PREG_UNMATCHED_AS_NULL|PREG_OFFSET_CAPTURE> $flags PREG_OFFSET_CAPTURE or PREG_UNMATCHED_AS_NULL, only available on PHP 7.4+
     *
     * @param-out int<0, max> $count
     */
    public static function replaceCallbackStrictGroups(string $pattern, callable $replacement, string $subject, int $limit = -1, int &$count = null, int $flags = 0): string
    {
        return self::replaceCallback($pattern, function (array $matches) use ($pattern, $replacement) {
            return $replacement(self::enforceNonNullMatches($pattern, $matches, 'replaceCallback'));
        }, $subject, $limit, $count, $flags);
    }

    /**
     *
     * @param array<string, callable(array<int|string, string|null>): string> $pattern
     * @param string $subject
     * @param int $count Set by method
     * @param int $flags
     *
     * @param-out int<0, max> $count
     */
    public static function replaceCallbackArray(array $pattern, string $subject, int $limit = -1, int &$count = null, int $flags = 0): string
    {
        $result = preg_replace_callback_array($pattern, $subject, $limit, $count, $flags | PREG_UNMATCHED_AS_NULL);
        if (is_null($result)) {
            $pattern = array_keys($pattern);
            throw PcreException::fromFunction("preg_replace_callback_array", $pattern);
        }
        return $result;
    }

    /**
     * @param string $pattern
     * @param string $subject
     * @param int $limit
     * @param int $flags
     * @throws PcreException
     * @return list<string>
     */
    public static function split(string $pattern, string $subject, int $limit = -1, int $flags = 0): array
    {
        $result = preg_split($pattern, $subject, $limit, $flags);
        if ($result === false)
            throw PcreException::fromFunction("preg_split", $pattern);

        return $result;
    }

    /**
     * @param string $pattern
     * @param string $subject
     * @param int $limit
     * @param int $flags
     * @throws PcreException
     * @return list<array{string,  int<0, max>}>
     */
    public static function splitWithOffsets(string $pattern, string $subject, int $limit = -1, int $flags = 0): array
    {
        $result = preg_split($pattern, $subject, $limit, $flags | PREG_SPLIT_OFFSET_CAPTURE);
        if ($result === false)
            throw PcreException::fromFunction("preg_split", $pattern);

        return $result;
    }

    /**
     * @template T of string|\Stringable
     * @param string $pattern
     * @param array<T> $array
     * @param int $flags
     * @return array<T>
     */
    public static function grep(string $pattern, array $array, int $flags = 0): array
    {
        $result = preg_grep($pattern, $array, $flags);
        if ($result === false)
            throw PcreException::fromFunction("preg_grep", $pattern);

        return $result;
    }

    /**
     * Variant de `match()` mais renvoie un bool au lieu d'un int.
     * 
     * @param non-empty-string $pattern
     * @param non-empty-string $subject
     * @param array<string|null> $matches
     * @param int-mask<PREG_UNMATCHED_AS_NULL|PREG_OFFSET_CAPTURE> $flags
     * @param int $offset
     * @return bool
     * 
     * @param-out array<int|string, string|null> $matches
     */
    public static function isMatch(string $pattern, string $subject, ?array &$matches = null, int $flags = 0, int $offset = 0): bool
    {
        return (bool) static::match($pattern, $subject, $matches, $flags, $offset);
    }

    /**
     * Une variante de la méthode `matchStrictGroups()` qui renvoie sortie non null our déclenche une erreur.
     * 
     * @param non-empty-string $pattern
     * @param non-empty-string $subject
     * @param array<string> $matches
     * @param int-mask<PREG_UNMATCHED_AS_NULL|PREG_OFFSET_CAPTURE> $flags
     * @param int $offset
     * @return bool
     * 
     * @param-out array<int|string, string> $matches
     */
    public static function isMatchStrictGroups(string $pattern, string $subject, ?array &$matches = null, int $flags = 0, int $offset = 0): bool
    {
        return (bool) static::matchStrictGroups($pattern, $subject, $matches, $flags, $offset);
    }

    /**
     * Une variante de `matchAll()` qui renvoie un bool.
     * 
     * @param non-empty-string $pattern
     * @param non-empty-string $subject
     * @param array<int|string, list<string|null>> $matches définis par la méthode.
     * @param int-mask<PREG_PATTERN_ORDER|PREG_SET_ORDER|PREG_OFFSET_CAPTURE|PREG_UNMATCHED_AS_NULL> $flags
     * @param int $offset
     * @return bool
     * 
     * @param-out array<int|string, list<string|null>> $matches
     */
    public static function isMatchAll(string $pattern, string $subject, ?array &$matches = null, int $flags = 0, int $offset = 0): bool
    {
        return (bool) static::matchAll($pattern, $subject, $matches, $flags, $offset);
    }

    /**
     * Une variante de `matchAllStrictGroup()` qui renvoie un bool.
     * 
     * @param non-empty-string $pattern
     * @param non-empty-string $subject
     * @param array<int|string, list<string|null>> $matches définis par la méthode.
     * @param int-mask<PREG_PATTERN_ORDER|PREG_SET_ORDER|PREG_OFFSET_CAPTURE|PREG_UNMATCHED_AS_NULL> $flags
     * @param int $offset
     * @return bool
     * 
     * @param-out array<int|string, list<string>> $matches
     */
    public static function isMatchAllStrictGroup(string $pattern, string $subject, ?array &$matches = null, int $flags = 0, int $offset = 0): bool
    {
        return (bool) static::matchAllStrictGroup($pattern, $subject, $matches, $flags, $offset);
    }

    /**
     * Une variante de `matchWithOffsets()` qui renvoie un bool.
     * 
     * @param non-empty-string $pattern
     * @param non-empty-string $subject
     * @param array<int|string, array{string|null, int}> $matches
     * @param int-mask<PREG_UNMATCHED_AS_NULL|PREG_OFFSET_CAPTURE> $flags
     * @param int $offset
     * @return bool
     * 
     * @param-out array<int|string, array{string|null, int<-1, max>}> $matches
     * @see \Composer\Pcre\matchWithOffsets
     */
    public static function isMatchWithOffsets(string $pattern, string $subject, ?array &$matches = null, int $flags = 0, int $offset = 0): bool
    {
        return (bool) static::matchWithOffsets($pattern, $subject, $matches, $flags, $offset);
    }

    /**
     * Exécuter preg_match avec PREG_OFFSET_CAPTURE
     * 
     * @param non-empty-string $pattern
     * @param non-empty-string $subject
     * @param array<int|string, list<array{string|null, int}>> $matches définis par la méthode.
     * @param int-mask<PREG_PATTERN_ORDER|PREG_SET_ORDER|PREG_OFFSET_CAPTURE|PREG_UNMATCHED_AS_NULL> $flags
     * @param int $offset
     * @return bool
     * 
     * @param-out array<int|string, list<array{string|null, int<-1, max>}>> $matches
     */
    public static function isMatchAllWithOffsets(string $pattern, string $subject, ?array &$matches = null, int $flags = 0, int $offset = 0): bool
    {
        return (bool) static::matchAllWithOffsets($pattern, $subject, $matches, $flags, $offset);
    }
    /**
     * @param int-mask<PREG_PATTERN_ORDER|PREG_SET_ORDER|PREG_OFFSET_CAPTURE|PREG_UNMATCHED_AS_NULL> $flags
     * @param string $userFunctionName
     * 
     * @throws \InvalidArgumentException
     * @return void
     */
    private static function checkOffsetCapture(int $flags, string $userFunctionName): void
    {
        if (($flags & PREG_OFFSET_CAPTURE) !== 0) {
            throw new \InvalidArgumentException("PREG_OFFSET_CAPTURE n'est pas pris en charge car il modifie le type de \$matches, utiliser"
                . " plutôt " . $userFunctionName . '() à la place.');
        }
    }

    /**
     * @param int-mask<PREG_PATTERN_ORDER|PREG_SET_ORDER|PREG_OFFSET_CAPTURE|PREG_UNMATCHED_AS_NULL> $flags
     * @return void
     */
    private static function checkSetOrder(int $flags): void
    {
        if (($flags & PREG_SET_ORDER) !== 0) {
            throw new \InvalidArgumentException('PREG_SET_ORDER n\'est pas pris en charge car il modifie le type de $matches');
        }
    }

    /**
     * @param non-empty-string      $pattern
     * @param array<int|string,     string|null> $matches
     * @param string                $variantMethod
     * @throws UnexpectedNullMatchException
     * 
     * @return array<int|string, string>
     */
    private static function enforceNonNullMatches(string $pattern, array $matches, string $variantMethod): array
    {
        foreach ($matches as $group => $match) {
            if (null === $match) {
                $txt = "Le pattern '%s' n'avais pas de correspondance qui est attendu pour le groupe : %s";
                $txt .= ", assurez-vous que le pattern à toujours des correspondances ou utiliser . '%s' à la place.";
                throw new UnexpectedNullMatchException(sprintf($txt, $pattern, $group, $variantMethod));
            }
        }
        /** @var array<int|string, string> */
        return $matches;
    }
    /**
     * @param non-empty-string      $pattern
     * @param array<int|string,     list<string|null>> $matches
     * @param string                $variantMethod
     * 
     * @return array<int|string, list<string>>
     * @throws UnexpectedNullMatchException
     */
    private static function enforceNonNullMatchAll(string $pattern, array $matches, string $variantMethod): array
    {
        foreach ($matches as $group => $groupMatch) {
            foreach ($groupMatch as $match) {
                if (is_null($match)) {
                    $txt = "Le pattern '%s' n'avais pas de correspondance qui est attendu pour le groupe : %s";
                    $txt .= ", assurez-vous que le pattern à toujours des correspondances ou utiliser . '%s' à la place.";
                    throw new UnexpectedNullMatchException(sprintf($txt, $pattern, $group, $variantMethod));
                }
            }
        }
        /** @var  array<int|string, list<string>> */
        return $matches;
    }
}