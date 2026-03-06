<?php

namespace Ngames\Framework\Tests;

use Ngames\Framework\Application;
use Ngames\Framework\Controller;
use Ngames\Framework\Request;
use Ngames\Framework\Router\Route;
use Ngames\Framework\Tests\Fixtures\TestAnnotatedController;
use PHPUnit\Framework\TestCase;

class ParameterInjectionTest extends TestCase
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

    public function testIntIdReceivesInteger()
    {
        $route = Route::create(TestAnnotatedController::class, 'showAction', ['id' => '42']);
        $request = new Request();
        $result = Controller::execute($route, $request);
        ob_start();
        $result->send();
        $content = ob_get_contents();
        ob_end_clean();

        $decoded = json_decode($content, true);
        $this->assertSame(42, $decoded['id']);
    }

    public function testMultipleParameters()
    {
        $route = Route::create(TestAnnotatedController::class, 'acceptAction', ['id' => '42', 'userId' => '7']);
        $request = new Request();
        $result = Controller::execute($route, $request);
        ob_start();
        $result->send();
        $content = ob_get_contents();
        ob_end_clean();

        $decoded = json_decode($content, true);
        $this->assertSame(42, $decoded['id']);
        $this->assertSame(7, $decoded['userId']);
    }

    public function testNoParametersWorksAsBeforeForAnnotated()
    {
        $route = Route::create(TestAnnotatedController::class, 'listAction');
        $request = new Request();
        $result = Controller::execute($route, $request);
        ob_start();
        $result->send();
        $content = ob_get_contents();
        ob_end_clean();

        $this->assertEquals('list', $content);
    }

    public function testMissingRequiredParameterReturns400()
    {
        // showAction requires int $id but we provide no parameters
        $route = Route::create(TestAnnotatedController::class, 'showAction');
        $request = new Request();
        $result = Controller::execute($route, $request);
        ob_start();
        $result->send();
        ob_end_clean();
        $this->assertEquals(400, http_response_code());
    }

    public function testOptionalParameterUsesDefault()
    {
        // detailsAction(int $id, string $format = 'json')
        $route = Route::create(TestAnnotatedController::class, 'detailsAction', ['id' => '42']);
        $request = new Request();
        $result = Controller::execute($route, $request);
        ob_start();
        $result->send();
        $content = ob_get_contents();
        ob_end_clean();

        $decoded = json_decode($content, true);
        $this->assertSame(42, $decoded['id']);
        $this->assertEquals('json', $decoded['format']);
    }

    public function testFloatCasting()
    {
        // scoreAction(float $id)
        $route = Route::create(TestAnnotatedController::class, 'scoreAction', ['id' => '3.14']);
        $request = new Request();
        $result = Controller::execute($route, $request);
        ob_start();
        $result->send();
        $content = ob_get_contents();
        ob_end_clean();

        $decoded = json_decode($content, true);
        $this->assertSame(3.14, $decoded['id']);
    }

    public function testBoolCasting()
    {
        // activeAction(bool $id)
        $route = Route::create(TestAnnotatedController::class, 'activeAction', ['id' => 'true']);
        $request = new Request();
        $result = Controller::execute($route, $request);
        ob_start();
        $result->send();
        $content = ob_get_contents();
        ob_end_clean();

        $decoded = json_decode($content, true);
        $this->assertSame(true, $decoded['id']);
    }
}
