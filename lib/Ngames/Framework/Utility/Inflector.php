<?php

/*
 * Copyright (c) 2014-2021 NGames
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is furnished
 * to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

namespace Ngames\Framework\Utility;

/**
 * Utility class to work with strings.
 *
 */
class Inflector
{
    /**
     * Transform a string to its camelized version.
     * Hello there => helloThere
     *
     * @param string $string
     */
    public static function camelize($string)
    {
        return lcfirst(str_replace(' ', '', self::humanize($string)));
    }

    /**
     * Transform a string to its underscored version.
     * HelloThere => hello_there
     *
     * @param string $string
     */
    public static function underscore($string)
    {
        return strtolower(preg_replace('/(?<=\\w)([A-Z])/', '_\\1', $string));
    }

    /**
     * Transform a string from underscore to human readable.
     * hello_there => Hello There
     *
     * @param string $string
     */
    public static function humanize($string)
    {
        return ucwords(str_replace('_', ' ', $string));
    }

    /**
     * Return an 's' or 'x' if the provided variable is greater than 1.
     *
     * @param int $variable
     * @param boolean $x
     * @return string
     */
    public static function pluralize($variable, $x = false)
    {
        if ($variable <= 1) {
            return '';
        }
        return $x ? 'x' : 's';
    }

    /**
     * Truncate the input and adds '...' to the end if needed.
     *
     * @param string $string
     * @param int $maxLength
     *            the maximum length over which ellipsis is done
     * @return string
     */
    public static function ellipsis($string, $maxLength)
    {
        if (mb_strlen($string) > $maxLength) {
            $string = substr($string, 0, $maxLength) . ' ...';
        }

        return $string;
    }
}
