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
 * Utility class to work with the filestystem.
 *
 */
class FileSystem
{
    /**
     * Deletes a file/folder recursively
     *
     * @param string $path
     */
    public static function unlink($path)
    {
        if (is_file($path)) {
            unlink($path);
        } elseif (is_dir($path)) {
            foreach (scandir($path) as $subPath) {
                if ($subPath != '.' && $subPath != '..') {
                    self::unlink($path . '/' . $subPath);
                }
            }

            rmdir($path);
        }
    }

    /**
     *
     * @param Resource $fileResource
     * @param string $string
     * @return int|false
     */
    public static function fwriteStream($fileResource, $string)
    {
        for ($written = 0; $written < strlen($string); $written += $fwrite) {
            $fwrite = fwrite($fileResource, substr($string, $written));
            if ($fwrite === false) {
                return false;
            }
        }

        return $written;
    }
}
