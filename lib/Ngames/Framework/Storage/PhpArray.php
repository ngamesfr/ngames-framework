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

/**
 * Simple wrapper over a PHP array.
 *
 */
class PhpArray extends AbstractStorage implements StorageInterface
{
    protected $storage;

    public function __construct(array $array = [])
    {
        $this->storage = $array;
    }

    public function has($name)
    {
        return array_key_exists($name, $this->storage);
    }

    public function set($name, $value)
    {
        $this->storage[$name] = $value;
    }

    public function get($name, $default = null)
    {
        return $this->has($name) ? $this->storage[$name] : $default;
    }

    public function reset()
    {
        $this->storage = [];
    }

    public function clear($name)
    {
        if ($this->has($name)) {
            unset($this->storage[$name]);
        }
    }
}
