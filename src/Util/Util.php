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
     * @return array The union of given sets.
     */
    public static function union(array ...$arrays): array
    {
        return array_unique(call_user_func_array('array_merge', $arrays));
    }

    /**
     * Determines whether two sets have a difference.
     *
     * @param array $first The first set.
     * @param array $second The second set.
     *
     * @return boolean Whether there is a difference.
     */
    public static function different(array $first, array $second): bool
    {
        return count(array_diff($first, $second)) !== 0;
    }

    /**
     * Determines length of a UTF-8 string.
     *
     * @param string $str The string in UTF-8 encoding.
     *
     * @return int The length.
     */
    public static function stringLength(string $str): int
    {
        return mb_strlen(mb_convert_encoding($str, 'ISO-8859-1'));
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
        static $lengthFunc = null;

        if ($lengthFunc === null) {
            $lengthFunc = function_exists('mb_substr') ? 'mb_substr' : 'iconv_substr';
        }

        if ($length === null) {
            $length = self::stringLength($str);
        }

        return $lengthFunc($str, $position, $length, 'UTF-8');
    }
}
