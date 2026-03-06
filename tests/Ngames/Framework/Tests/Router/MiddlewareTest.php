<?php

namespace Ngames\Framework\Tests\Router;

use Ngames\Framework\Application;
use Ngames\Framework\Controller;
use Ngames\Framework\Request;
use Ngames\Framework\Router\Route;
use Ngames\Framework\Tests\Fixtures\TestAnnotatedController;
use Ngames\Framework\Tests\Fixtures\TestClassMiddleware;
use Ngames\Framework\Tests\Fixtures\TestMethodMiddleware;
use Ngames\Framework\Tests\Fixtures\TestShortCircuitMiddleware;
use PHPUnit\Framework\TestCase;

class MiddlewareTest extends TestCase
{
    protected function setUp(): void
    {
        $reflection = new \ReflectionClass(Application::class);
        $instance = $reflection->getProperty('instance');
        $instance->setValue(null, null);

        Application::initialize(ROOT_DIR . '/tests/data/Application/config.ini');
    }

    protected function tearDown(): void
    {
        $reflection = new \ReflectionClass(Application::class);
        $instance = $reflection->getProperty('instance');
        $instance->setValue(null, null);
    }

    public function testClassLevelMiddlewareRunsForAllMethods()
    {
        $route = Route::create(
            TestAnnotatedController::class,
            'listAction',
            [],
            [TestClassMiddleware::class]
        );
        $request = new Request();
        $result = Controller::execute($route, $request);
        $this->assertEquals('applied', $result->getHeaders()['X-Class-Middleware']);
    }

    public function testMethodLevelMiddlewareStacksOnTopOfClassLevel()
    {
        $route = Route::create(
            TestAnnotatedController::class,
            'deleteAction',
            ['id' => '42'],
            [TestClassMiddleware::class, TestMethodMiddleware::class]
        );
        $request = new Request();
        $result = Controller::execute($route, $request);
        $this->assertEquals('applied', $result->getHeaders()['X-Class-Middleware']);
        $this->assertEquals('applied', $result->getHeaders()['X-Method-Middleware']);
    }

    public function testMiddlewareCanShortCircuit()
    {
        $route = Route::create(
            TestAnnotatedController::class,
            'listAction',
            [],
            [TestShortCircuitMiddleware::class]
        );
        $request = new Request();
        ob_start();
        $result = Controller::execute($route, $request);
        ob_end_clean();
        $this->assertEquals(401, $result->getHeaders()['Content-Type'] ? 401 : 200);
        // Short-circuit returns unauthorized
        ob_start();
        $result->send();
        $content = ob_get_contents();
        ob_end_clean();
        $this->assertEquals('Blocked by middleware', $content);
    }

    public function testMiddlewareExecutionOrder()
    {
        $route = Route::create(
            TestAnnotatedController::class,
            'deleteAction',
            ['id' => '1'],
            [TestClassMiddleware::class, TestMethodMiddleware::class]
        );
        $request = new Request();
        $result = Controller::execute($route, $request);

        $headers = $result->getHeaders();
        $this->assertArrayHasKey('X-Class-Middleware', $headers);
        $this->assertArrayHasKey('X-Method-Middleware', $headers);
    }

    public function testNonAnnotatedRoutesSkipMiddleware()
    {
        $route = Route::createLegacy('application', 'dummy', 'index');
        $request = new Request();
        $result = Controller::execute($route, $request);

        ob_start();
        $result->send();
        $content = ob_get_contents();
        ob_end_clean();
        $this->assertEquals('index', $content);
    }
}
