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

use Ngames\Framework\Application;
use Ngames\Framework\Exception;
use Ngames\Framework\Storage\IniFile;
use Ngames\Framework\Timer;
use Ngames\Framework\Router\Router;
use Ngames\Framework\Router\Matcher;
use Ngames\Framework\Logger;

class ApplicationTest extends \PHPUnit\Framework\TestCase
{
    public function setUp(): void
    {
        // Reset the instance
        $reflection = new \ReflectionClass(Application::class);
        $instance = $reflection->getProperty('instance');
        $instance->setValue(null, null);
        Logger::initialize(null, Logger::LEVEL_ERROR);
    }

    public function testInitializeGetInstance()
    {
        $application = Application::initialize(ROOT_DIR . '/tests/data/Application/config.ini');
        $this->assertEquals($application, Application::getInstance());
    }

    public function testInitialize_alreadyInitialized()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('The application has already been initialized');
        Application::initialize(ROOT_DIR . '/tests/data/Application/config.ini');
        Application::initialize(ROOT_DIR . '/tests/data/Application/config.ini');
    }

    public function testGetInstance_notInitialized()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('The application has not been initialized');
        Application::getInstance();
    }

    public function testGetConfiguration()
    {
        $configuration = new IniFile(ROOT_DIR . '/tests/data/Application/config.ini');
        $application = Application::initialize(ROOT_DIR . '/tests/data/Application/config.ini');
        $this->assertEquals($configuration, $application->getConfiguration());
    }

    public function testGetTimer()
    {
        $application = Application::initialize(ROOT_DIR . '/tests/data/Application/config.ini');
        $this->assertInstanceOf(Timer::class, $application->getTimer());
    }

    public function testGetRouter()
    {
        $application = Application::initialize(ROOT_DIR . '/tests/data/Application/config.ini');
        $this->assertInstanceOf(Router::class, $application->getRouter());
    }

    public function testIsDebugDisabled()
    {
        $this->assertTrue(Application::initialize(ROOT_DIR . '/tests/data/Application/config_debug.ini')->isDebug());
    }

    public function testIsDebugEnabled()
    {
        $this->assertTrue(Application::initialize(ROOT_DIR . '/tests/data/Application/config_debug.ini')->isDebug());
    }

    public function testLoggerInitialization()
    {
        Application::initialize(ROOT_DIR . '/tests/data/Application/config_log.ini');
        ob_start();
        Logger::logDebug('debug');
        $this->assertStringContainsString('ApplicationTest.php:' . (__LINE__ - 1) . ' - debug', ob_get_contents());
        ob_end_clean();
    }

    public function testRun()
    {
        require_once __DIR__ . '/DummyController.php';
        $application = Application::initialize(ROOT_DIR . '/tests/data/Application/config.ini');
        $application->getRouter()->addMatcher(new Matcher('/', 'application', 'dummy', 'index'));
        ob_start();
        $application->run();
        $this->assertEquals('index', ob_get_contents());
        $this->assertEquals(200, http_response_code());
        ob_end_clean();
    }

    public function testRun_actionNotFound()
    {
        require_once __DIR__ . '/DummyController.php';
        $application = Application::initialize(ROOT_DIR . '/tests/data/Application/config.ini');
        $application->getRouter()->addMatcher(new Matcher('/test', 'application', 'dummy', 'index'));
        ob_start();
        $application->run();
        $this->assertEquals('File not found.', ob_get_contents());
        $this->assertEquals(404, http_response_code());
        ob_end_clean();
    }

    public function testRun_actionReturnString()
    {
        require_once __DIR__ . '/DummyController.php';
        $application = Application::initialize(ROOT_DIR . '/tests/data/Application/config.ini');
        $application->getRouter()->addMatcher(new Matcher('/', 'application', 'dummy', 'output-string'));
        ob_start();
        $application->run();
        $this->assertEquals('output_string', ob_get_contents());
        $this->assertEquals(200, http_response_code());
        ob_end_clean();
    }

    public function testRun_actionReturnNull()
    {
        require_once __DIR__ . '/DummyController.php';
        $application = Application::initialize(ROOT_DIR . '/tests/data/Application/config.ini');
        $application->getRouter()->addMatcher(new Matcher('/', 'application', 'dummy', 'output-null'));
        ob_start();
        $application->run();
        $this->assertEquals('Internal server error.', ob_get_contents());
        $this->assertEquals(500, http_response_code());
        ob_end_clean();
    }
}
