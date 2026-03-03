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

namespace Ngames\Framework\Storage;

use Ngames\Framework\Exception;

/**
 * Create a new storage instance based on a path to an INI file.
 *
 */
class IniFile extends PhpArrayRecursive implements StorageInterface
{
    /**
     *
     * @param string $fileName
     *            the filename to load
     * @throws Exception
     */
    public function __construct($fileName)
    {
        if (!is_readable($fileName)) {
            throw new Exception($fileName . ' is not readable');
        }

        $parsedFile = parse_ini_file($fileName, true);
        $processedArray = [];

        if ($parsedFile !== false) {
            $processedArray = $this->processParsedFile($parsedFile);
        }

        parent::__construct($processedArray);
    }

    /**
     * Persist the configuration to a filename
     *
     * @param string $fileName
     * @param array $configuration
     */
    public static function writeFile($fileName, $configuration)
    {
        $content = '';

        foreach ($configuration as $key => $value) {
            $content .= $key . '=' . $value . "\n";
        }

        file_put_contents($fileName, $content);
    }

    protected function processParsedFile(array $array)
    {
        $result = [];

        foreach ($array as $key => $value) {
            $currentResult = &$result;

            if (is_int($key)) {
                $currentResult = &$currentResult[$key];
            } else {
                $keyPartArray = explode('.', $key);

                while ($keyPart = array_shift($keyPartArray)) {
                    $currentResult = &$currentResult[$keyPart];
                }
            }

            if (is_array($value)) {
                $currentResult = $this->processParsedFile($value);
            } else {
                if (strpos($value, '%') === false) {
                    $currentResult = $value;
                } else {
                    $currentResult = preg_replace_callback('/%(.*?)%/s', function ($match) {
                        if (getenv($match[1])) {
                            return getenv($match[1]);
                        } elseif (defined($match[1])) {
                            return constant($match[1]);
                        } else {
                            return $match[0];
                        }
                    }, $value);
                }
            }
        }

        return $result;
    }
}
