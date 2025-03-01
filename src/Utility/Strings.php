<?php
declare(strict_types=1);

namespace DR\Review\Utility;

class Strings
{
    /**
     * Find the matching prefix between the 2 strings
     */
    public static function findPrefix(string $left, string $right): string
    {
        // iterate over the shortest string length
        $shortest = strlen($left) > strlen($right) ? $right : $left;
        $length   = strlen($shortest);

        if ($length === 0) {
            return '';
        }

        // find the first occurrence of left differs from right
        for ($i = 0; $i < $length; $i++) {
            if ($left[$i] !== $right[$i]) {
                return substr($shortest, 0, $i);
            }
        }

        return $shortest;
    }

    /**
     * Find the matching suffix between the 2 strings
     */
    public static function findSuffix(string $left, string $right): string
    {
        return strrev(self::findPrefix(strrev($left), strrev($right)));
    }

    /**
     * Replace prefix and suffix in string
     */
    public static function replace(string $string, string $prefix, string $suffix): string
    {
        $string = self::replacePrefix($string, $prefix);

        return self::replaceSuffix($string, $suffix);
    }

    public static function replacePrefix(string $string, string $prefix): string
    {
        return (string)preg_replace('#^' . preg_quote($prefix, '#') . '#', '', $string);
    }

    public static function replaceSuffix(string $string, string $suffix): string
    {
        return (string)preg_replace('#' . preg_quote($suffix, '#') . '$#', '', $string);
    }

    /**
     * Test if all substrings are part of the string
     *
     * @param string[] $substrings
     */
    public static function contains(string $string, array $substrings): bool
    {
        if (count($substrings) === 0) {
            return false;
        }

        foreach ($substrings as $substring) {
            if (stripos($string, $substring) === false) {
                return false;
            }
        }

        return true;
    }
}
