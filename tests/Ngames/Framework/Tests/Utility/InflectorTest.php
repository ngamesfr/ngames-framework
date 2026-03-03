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

namespace Ngames\Framework\Tests\Utility;

use Ngames\Framework\Utility\Inflector;

class InflectorTest extends \PHPUnit\Framework\TestCase
{
    public function testCamelize()
    {
        $this->assertEquals('helloThere', Inflector::camelize('hello there'));
        $this->assertEquals('helloThere', Inflector::camelize('helloThere'));
        $this->assertEquals('helloThere', Inflector::camelize('HelloThere'));
        $this->assertEquals('helloThere', Inflector::camelize('hello_there'));
    }

    public function testUnderscore()
    {
        $this->assertEquals('hello_there', Inflector::underscore('helloThere'));
        $this->assertEquals('hello_there', Inflector::underscore('HelloThere'));
        $this->assertEquals('hello_there', Inflector::underscore('hello_there'));
    }

    public function testHumanize()
    {
        $this->assertEquals('Hello There', Inflector::humanize('hello there'));
        $this->assertEquals('Hello There', Inflector::humanize('hello_there'));
    }

    public function testPluralize()
    {
        $this->assertEquals('', Inflector::pluralize(0));
        $this->assertEquals('', Inflector::pluralize(1));
        $this->assertEquals('s', Inflector::pluralize(2));
        $this->assertEquals('x', Inflector::pluralize(2, true));
    }

    public function testEllipsis()
    {
        $this->assertEquals('test', Inflector::ellipsis('test', 10));
        $this->assertEquals('test', Inflector::ellipsis('test', 4));
        $this->assertEquals('test ...', Inflector::ellipsis('testtest', 4));
        $this->assertEquals('tes ...', Inflector::ellipsis('test', 3));
    }
}
