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

namespace Ngames\Framework\Tests\Storage;

use Ngames\Framework\Storage\PhpArray;

class PhpArrayTest extends \PHPUnit\Framework\TestCase
{
    public function testHas()
    {
        $this->assertFalse((new PhpArray())->has('test'));
        $this->assertTrue((new PhpArray(['test' => 'value']))->has('test'));
        $this->assertFalse((new PhpArray(['test']))->has('test'));
        $this->assertTrue((new PhpArray(['test']))->has(0));
    }

    public function testSet()
    {
        $phpArray = new PhpArray();
        $this->assertFalse($phpArray->has('test'));
        $phpArray->set('test', 'value');
        $this->assertTrue($phpArray->has('test'));
    }

    public function testGet()
    {
        $phpArray = new PhpArray(['test' => 'value']);
        $this->assertEquals('value', $phpArray->get('test'));
        $this->assertNull($phpArray->get('test2'));
        $this->assertEquals('default', $phpArray->get('test2', 'default'));
    }

    public function testClear()
    {
        $phpArray = new PhpArray(['test' => 'value']);
        $this->assertTrue($phpArray->has('test'));
        $phpArray->clear('test');
        $this->assertFalse($phpArray->has('test'));
    }

    public function testReset()
    {
        $phpArray = new PhpArray(['test' => 'value']);
        $this->assertTrue($phpArray->has('test'));
        $phpArray->reset();
        $this->assertFalse($phpArray->has('test'));
    }
}
