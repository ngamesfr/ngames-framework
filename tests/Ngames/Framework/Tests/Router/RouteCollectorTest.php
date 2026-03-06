<?php

namespace Ngames\Framework\Tests\Router;

use Ngames\Framework\Router\Router;
use Ngames\Framework\Router\RouteCollector;
use Ngames\Framework\Tests\Fixtures\TestAnnotatedController;
use PHPUnit\Framework\TestCase;

class RouteCollectorTest extends TestCase
{
    private string $fixturesDir;

    protected function setUp(): void
    {
        $this->fixturesDir = __DIR__ . '/../Fixtures';
    }

    public function testScansAnnotatedControllers()
    {
        $router = new Router();
        $collector = new RouteCollector();
        $collector->collect([$this->fixturesDir], $router);

        // Should find the GET /api/v1/alliances route
        $route = $router->getRoute('/api/v1/alliances', 'GET');
        $this->assertNotNull($route);
        $this->assertTrue($route->isAnnotated());
        $this->assertEquals(TestAnnotatedController::class, $route->getControllerClass());
    }

    public function testConcatenatesClassAndMethodPaths()
    {
        $router = new Router();
        $collector = new RouteCollector();
        $collector->collect([$this->fixturesDir], $router);

        $route = $router->getRoute('/api/v1/alliances/42', 'GET');
        $this->assertNotNull($route);
        $this->assertEquals('showAction', $route->getActionMethod());
        $this->assertEquals(['id' => '42'], $route->getParameters());
    }

    public function testRegistersMatchersWithHttpMethodConstraints()
    {
        $router = new Router();
        $collector = new RouteCollector();
        $collector->collect([$this->fixturesDir], $router);

        // GET and DELETE both match /api/v1/alliances/:id but with different methods
        $getRoute = $router->getRoute('/api/v1/alliances/42', 'GET');
        $this->assertNotNull($getRoute);
        $this->assertEquals('showAction', $getRoute->getActionMethod());

        $deleteRoute = $router->getRoute('/api/v1/alliances/42', 'DELETE');
        $this->assertNotNull($deleteRoute);
        $this->assertEquals('deleteAction', $deleteRoute->getActionMethod());
    }

    public function testCapturesPathParameters()
    {
        $router = new Router();
        $collector = new RouteCollector();
        $collector->collect([$this->fixturesDir], $router);

        $route = $router->getRoute('/api/v1/alliances/42/members/7/accept', 'POST');
        $this->assertNotNull($route);
        $this->assertEquals('acceptAction', $route->getActionMethod());
        $this->assertEquals(['id' => '42', 'userId' => '7'], $route->getParameters());
    }

    public function testSkipsClassesWithoutRouteAttribute()
    {
        $router = new Router();
        $collector = new RouteCollector();
        $collector->collect([$this->fixturesDir], $router);

        // TestNoAttributeController has no #[Route], so it shouldn't register anything
        // We can verify by checking that only annotated routes exist
        $this->assertNull($router->getRoute('/no-attribute-path', 'GET'));
    }

    public function testCollectsMiddlewares()
    {
        $router = new Router();
        $collector = new RouteCollector();
        $collector->collect([$this->fixturesDir], $router);

        // The delete route has both class-level and method-level middleware
        $route = $router->getRoute('/api/v1/alliances/42', 'DELETE');
        $this->assertNotNull($route);
        $middlewares = $route->getMiddlewares();
        $this->assertCount(2, $middlewares);
    }

    public function testClearCache()
    {
        $collector = new RouteCollector();
        // Should not throw even if APCu is not available
        $collector->clearCache();
        $this->assertTrue(true);
    }

    public function testNonExistentDirectory()
    {
        $router = new Router();
        $collector = new RouteCollector();
        $collector->collect(['/non/existent/directory'], $router);

        $this->assertNull($router->getRoute('/anything', 'GET'));
    }

    public function testPatchMethodRoutes()
    {
        $router = new Router();
        $collector = new RouteCollector();
        $collector->collect([$this->fixturesDir], $router);

        $route = $router->getRoute('/api/v1/alliances/42', 'PATCH');
        $this->assertNotNull($route);
        $this->assertEquals('updateAction', $route->getActionMethod());
        $this->assertEquals(['id' => '42'], $route->getParameters());
    }
}
