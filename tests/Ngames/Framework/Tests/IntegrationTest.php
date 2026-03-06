<?php

namespace Ngames\Framework\Tests;

use Ngames\Framework\Application;
use Ngames\Framework\Controller;
use Ngames\Framework\Request;
use Ngames\Framework\Router\RouteCollector;
use Ngames\Framework\Router\Router;
use Ngames\Framework\Tests\Fixtures\TestAnnotatedController;
use PHPUnit\Framework\TestCase;

class IntegrationTest extends TestCase
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

    public function testFullFlowWithRouteCollectorAndDispatch()
    {
        $router = new Router();
        $collector = new RouteCollector();
        $collector->collect([__DIR__ . '/Fixtures'], $router);

        // Match the route
        $route = $router->getRoute('/api/v1/alliances/42', 'GET');
        $this->assertNotNull($route);
        $this->assertTrue($route->isAnnotated());
        $this->assertEquals(TestAnnotatedController::class, $route->getControllerClass());
        $this->assertEquals('showAction', $route->getActionMethod());
        $this->assertEquals(['id' => '42'], $route->getParameters());

        // Dispatch through Controller
        $request = new Request();
        $result = Controller::execute($route, $request);
        ob_start();
        $result->send();
        $content = ob_get_contents();
        ob_end_clean();

        $decoded = json_decode($content, true);
        $this->assertSame(42, $decoded['id']);
    }

    public function testFullFlowWithMiddleware()
    {
        $router = new Router();
        $collector = new RouteCollector();
        $collector->collect([__DIR__ . '/Fixtures'], $router);

        // DELETE route has class + method middleware
        $route = $router->getRoute('/api/v1/alliances/42', 'DELETE');
        $this->assertNotNull($route);

        $request = new Request();
        $result = Controller::execute($route, $request);

        // Verify middleware ran
        $headers = $result->getHeaders();
        $this->assertEquals('applied', $headers['X-Class-Middleware']);
        $this->assertEquals('applied', $headers['X-Method-Middleware']);

        // Verify correct action called with typed parameter
        ob_start();
        $result->send();
        $content = ob_get_contents();
        ob_end_clean();

        $decoded = json_decode($content, true);
        $this->assertSame(42, $decoded['deleted']);
    }

    public function testAnnotatedRoutesCoexistWithConventionRoutes()
    {
        $router = new Router();
        $collector = new RouteCollector();
        $collector->collect([__DIR__ . '/Fixtures'], $router);

        // Add a convention matcher after annotated routes
        $router->addMatcher(new \Ngames\Framework\Router\Matcher('/:module/:controller/:action'));

        // Annotated route should match
        $route = $router->getRoute('/api/v1/alliances', 'GET');
        $this->assertNotNull($route);
        $this->assertTrue($route->isAnnotated());

        // Convention route should still work
        $route = $router->getRoute('/app/home/index', 'GET');
        $this->assertNotNull($route);
        $this->assertFalse($route->isAnnotated());
        $this->assertEquals('app', $route->getModuleName());
    }

    public function testShortCircuitMiddlewarePreventsAction()
    {
        $router = new Router();
        $collector = new RouteCollector();
        $collector->collect([__DIR__ . '/Fixtures'], $router);

        $route = $router->getRoute('/api/v1/blocked', 'GET');
        $this->assertNotNull($route);

        $request = new Request();
        $result = Controller::execute($route, $request);

        ob_start();
        $result->send();
        $content = ob_get_contents();
        ob_end_clean();

        $this->assertEquals(401, http_response_code());
        $this->assertEquals('Blocked by middleware', $content);
    }
}
