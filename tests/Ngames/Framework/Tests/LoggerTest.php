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

namespace Ngames\Framework\Tests;

use Ngames\Framework\Logger;

class LoggerTest extends \PHPUnit\Framework\TestCase
{
    private $expectedMessages = array();

    private $notExpectedMessages = array();

    public function setUp(): void
    {
        ob_start();
    }

    public function tearDown(): void
    {
        $output = ob_get_contents();
        ob_end_clean();

        foreach ($this->expectedMessages as $expectedMessage) {
            $this->assertStringContainsString($expectedMessage, $output);
        }
        foreach ($this->notExpectedMessages as $notExpectedMessage) {
            $this->assertStringNotContainsString($notExpectedMessage, $output);
        }
    }

    public function testInitialize()
    {
        Logger::initialize('php://output', Logger::LEVEL_INFO);
        Logger::logDebug('debug message');
        Logger::logInfo('info message');
        Logger::logWarning('warning message');
        Logger::logError('error message');
        $this->expectedMessages = array(
            'info message',
            'warning message',
            'error message'
        );
        $this->notExpectedMessages = array(
            'debug message'
        );
    }

    public function testSetDestination_error()
    {
        $this->expectException('\Ngames\Framework\Exception');
        $this->expectExceptionMessage('Cannot open log file for writing');
        Logger::setDestination('\\//');
    }

    public function test_minLevelDebug()
    {
        Logger::setDestination('php://output');
        Logger::setMinLevel(Logger::LEVEL_DEBUG);
        Logger::logDebug('debug message');
        Logger::logInfo('info message');
        Logger::logWarning('warning message');
        Logger::logError('error message');
        $this->expectedMessages = array(
            'debug message',
            'info message',
            'warning message',
            'error message'
        );
        $this->notExpectedMessages = array();
    }

    public function test_minLevelInfo()
    {
        Logger::setDestination('php://output');
        Logger::setMinLevel(Logger::LEVEL_INFO);
        Logger::logDebug('debug message');
        Logger::logInfo('info message');
        Logger::logWarning('warning message');
        Logger::logError('error message');
        $this->expectedMessages = array(
            'info message',
            'warning message',
            'error message'
        );
        $this->notExpectedMessages = array(
            'debug message'
        );
    }

    public function test_minLevelWarning()
    {
        Logger::setDestination('php://output');
        Logger::setMinLevel(Logger::LEVEL_WARNING);
        Logger::logDebug('debug message');
        Logger::logInfo('info message');
        Logger::logWarning('warning message');
        Logger::logError('error message');
        $this->expectedMessages = array(
            'warning message',
            'error message'
        );
        $this->notExpectedMessages = array(
            'debug message',
            'info message'
        );
    }

    public function test_minLevelError()
    {
        Logger::setDestination('php://output');
        Logger::setMinLevel(Logger::LEVEL_ERROR);
        Logger::logDebug('debug message');
        Logger::logInfo('info message');
        Logger::logWarning('warning message');
        Logger::logError('error message');
        $this->expectedMessages = array(
            'error message'
        );
        $this->notExpectedMessages = array(
            'debug message',
            'info message',
            'warning message'
        );
    }

    public function test_logFormat()
    {
        Logger::setDestination('php://output');
        Logger::setMinLevel(Logger::LEVEL_DEBUG);
        Logger::logDebug('debug message');
        $lineNumber = __LINE__ - 1;
        $this->expectedMessages = array(
            '[DEBUG] ' . DIRECTORY_SEPARATOR . 'tests' . DIRECTORY_SEPARATOR . 'Ngames' . DIRECTORY_SEPARATOR . 'Framework' . DIRECTORY_SEPARATOR . 'Tests' . DIRECTORY_SEPARATOR . 'LoggerTest.php:' . $lineNumber . ' - debug message'
        );
    }
}
