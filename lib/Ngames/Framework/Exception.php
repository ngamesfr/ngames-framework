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

namespace Ngames\Framework;

/**
 * Basic exception class only to define the namespace at framework level (for specific catches).
 */
class Exception extends \Exception
{
    public function __construct($message = null, $code = 1, $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    /**
     * Enhanced trace printer for exceptions.
     * Strongly inspired by http://php.net/manual/fr/exception.gettraceasstring.php#114980.
     *
     * @param \Exception $e
     * @param array $seen
     *
     * @return string
     */
    public static function trace($e, array $seen = [])
    {
        $starter = $seen ? 'Caused by: ' : '';
        $result = [];
        $trace = $e->getTrace();
        $result[] = sprintf('%s%s: %s', $starter, get_class($e), $e->getMessage() == '' ? '[no message set]' : $e->getMessage());
        $file = $e->getFile();
        $line = $e->getLine();

        while (true) {
            $current = sprintf('%s:%s', $file, $line);

            if (in_array($current, $seen)) {
                $result[] = sprintf('    ... %d more', count($trace) + 1);
                break;
            }

            $function = count($trace) && array_key_exists('function', $trace[0])
                ? self::formatFunction($trace[0])
                : '(main)';

            $location = str_replace(ROOT_DIR, "", $file) . ($line === null ? '' : ':' . $line);
            $result[] = sprintf('    at %s (%s)', $function, $location);
            $seen[] = $current;

            if (!count($trace)) {
                break;
            }

            $file = array_key_exists('file', $trace[0]) ? $trace[0]['file'] : 'Unknown Source';
            $line = array_key_exists('file', $trace[0]) && array_key_exists('line', $trace[0]) && $trace[0]['line'] ? $trace[0]['line'] : null;
            array_shift($trace);
        }

        $result = implode("\n", $result);

        if ($e->getPrevious()) {
            $result .= "\n" . self::trace($e->getPrevious(), $seen);
        }

        return $result;
    }

    private static function formatFunction(array $frame): string
    {
        $args = [];

        if (array_key_exists('args', $frame) && is_array($frame['args'])) {
            $args = array_map([self::class, 'formatArgument'], $frame['args']);
        }

        $function = array_key_exists('class', $frame) ? $frame['class'] . '::' : '';
        $function .= $frame['function'];

        return $function . '(' . implode(', ', $args) . ')';
    }

    private static function formatArgument($arg): string
    {
        if (is_scalar($arg) || is_array($arg)) {
            return preg_replace('/\s+/', ' ', str_replace("\n", '', var_export($arg, true)));
        }

        if (is_object($arg)) {
            return get_class($arg);
        }

        return gettype($arg);
    }
}
