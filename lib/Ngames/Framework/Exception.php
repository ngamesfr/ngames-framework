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

            // Stop if already displayed
            if (in_array($current, $seen)) {
                $result[] = sprintf('    ... %d more', count($trace) + 1);
                break;
            }

            // Add the current formatted trace element
            if (count($trace) && array_key_exists('function', $trace[0])) {
                $args = [];

                if (count($trace) && array_key_exists('args', $trace[0]) && is_array($trace[0]['args'])) {
                    $args = array_map(function ($arg) {
                        if (is_scalar($arg) || is_array($arg)) {
                            return preg_replace('/\s+/', ' ', str_replace([
                                "\n"
                            ], '', var_export($arg, true)));
                        } elseif (is_object($arg)) {
                            return get_class($arg);
                        } else {
                            return gettype($arg);
                        }
                    }, $trace[0]['args']);
                }

                // Build the function with args
                $function = array_key_exists('class', $trace[0]) ? $trace[0]['class'] . '::' : '';
                $function .= $trace[0]['function'];
                $function .= '(' . implode(', ', $args) . ')';
            } else {
                $function = '(main)';
            }

            $location = str_replace(ROOT_DIR, "", $file) . ($line === null ? '' : ':' . $line);
            $result[] = sprintf('    at %s (%s)', $function, $location);
            $seen[] = $current;

            // Reached the end
            if (!count($trace)) {
                break;
            }

            // Get the next trace element
            $file = array_key_exists('file', $trace[0]) ? $trace[0]['file'] : 'Unknown Source';
            $line = array_key_exists('file', $trace[0]) && array_key_exists('line', $trace[0]) && $trace[0]['line'] ? $trace[0]['line'] : null;
            array_shift($trace);
        }

        $result = implode("\n", $result);

        // Append previous exception trace
        if ($e->getPrevious()) {
            $result .= "\n" . self::trace($e->getPrevious(), $seen);
        }

        return $result;
    }
}
