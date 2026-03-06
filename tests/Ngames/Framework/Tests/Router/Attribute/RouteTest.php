<?php

namespace Ngames\Framework\Tests\Router\Attribute;

use Ngames\Framework\Router\Attribute\Route;
use PHPUnit\Framework\TestCase;

class RouteTest extends TestCase
{
    public function testStoresPath()
    {
        $route = new Route('/api/v1/users');
        $this->assertEquals('/api/v1/users', $route->path);
    }

    public function testDefaultEmptyPath()
    {
        $route = new Route();
        $this->assertEquals('', $route->path);
    }

    public function testIsClassLevelAttribute()
    {
        $ref = new \ReflectionClass(Route::class);
        $attrs = $ref->getAttributes(\Attribute::class);
        $this->assertCount(1, $attrs);
        $attr = $attrs[0]->newInstance();
        $this->assertEquals(\Attribute::TARGET_CLASS, $attr->flags);
    }
}
