<?php

declare(strict_types=1);

namespace Dissect\Util;

/**
 * Some utility functions.
 *
 * @author Jakub Lédl <jakubledl@gmail.com>
 */
abstract class Util
{
    /**
     * Merges two or more sets by values.
     *
     * {a, b} union {b, c} = {a, b, c}
     *
     * @template T of string
     * @param array<T> ...$arrays
     * @return array<T> The union of given sets.
     */
    public static function union(array ...$arrays): array
    {
        return array_unique(array_merge(...$arrays));
    }

    /**
     * Determines whether two sets have a difference.
     *
     * @template T of string
     * @param array<T> $first The first set.
     * @param array<T> $second The second set.
     *
     * @return bool Whether there is a difference.
     */
    public static function different(array $first, array $second): bool
    {
        return count(array_diff($first, $second)) !== 0;
    }

    /**
     * Determines the byte length of a string value (for position tracking).
     *
     * @param int|string $str The value.
     */
    public static function stringLength(int|string $str): int
    {
        return strlen((string) $str);
    }

    /**
     * Extracts a substring of a UTF-8 string.
     *
     * @param string $str The string to extract the substring from.
     * @param int $position The position from which to start extracting.
     * @param int|null $length The length of the substring.
     *
     * @return string The substring.
     */
    public static function substring(string $str, int $position, ?int $length = null): string
    {
        if ($length === null) {
            $length = self::stringLength($str);
        }

        return mb_substr($str, $position, $length, 'UTF-8');
    }
}
