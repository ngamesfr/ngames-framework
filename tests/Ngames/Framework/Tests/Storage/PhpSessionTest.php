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

use Ngames\Framework\Storage\PhpSession;

class PhpSessionTest extends \PHPUnit\Framework\TestCase
{
    public function setUp(): void
    {
        PhpSession::clearInstance();
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION['test'] = 'value';
    }


    public function testGetter()
    {
        $session = new PhpSession();
        $this->assertTrue($session->has('test'));
        $this->assertEquals('value', $session->get('test'));
        $this->assertNull($session->get('test2'));
        $this->assertEquals('default', $session->get('test2', 'default'));
    }


    public function testGetInstance()
    {
        $session = PhpSession::getInstance();
        $this->assertTrue($session->has('test'));
        $this->assertEquals('value', $session->get('test'));
    }


    public function testSetter()
    {
        $session = new PhpSession();
        $this->assertFalse($session->has('test2'));
        $session->test2 = 'value2';
        $this->assertTrue($session->has('test2'));
        $this->assertEquals('value2', $session->get('test2'));
    }


    public function testResetSession()
    {
        $session = new PhpSession();
        $this->assertTrue($session->has('test'));
        $session->reset();
        $this->assertFalse($session->has('test'));
    }
}
