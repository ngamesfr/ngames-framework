<?php

namespace Ngames\Framework\Tests\Router\Attribute;

use Ngames\Framework\Router\Attribute\Middleware;
use PHPUnit\Framework\TestCase;

class MiddlewareTest extends TestCase
{
    public function testStoresClass()
    {
        $attr = new Middleware('App\\Middleware\\RequireAuth');
        $this->assertEquals('App\\Middleware\\RequireAuth', $attr->class);
    }

    public function testIsRepeatableAndWorksOnClassAndMethod()
    {
        $ref = new \ReflectionClass(Middleware::class);
        $attrs = $ref->getAttributes(\Attribute::class);
        $this->assertCount(1, $attrs);
        $attr = $attrs[0]->newInstance();
        $expectedFlags = \Attribute::TARGET_CLASS | \Attribute::TARGET_METHOD | \Attribute::IS_REPEATABLE;
        $this->assertEquals($expectedFlags, $attr->flags);
    }
}
