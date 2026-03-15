<?php

namespace Ngames\Framework\Tests\Router\Attribute;

use Ngames\Framework\Router\Attribute\Middleware;
use Ngames\Framework\Tests\Fixtures\TestClassMiddleware;
use Ngames\Framework\Tests\Fixtures\TestMethodMiddleware;
use PHPUnit\Framework\TestCase;

class MiddlewareTest extends TestCase
{
    public function testStoresSingleInstance()
    {
        $mw = new TestClassMiddleware();
        $attr = new Middleware($mw);
        $this->assertCount(1, $attr->instances);
        $this->assertSame($mw, $attr->instances[0]);
    }

    public function testStoresMultipleInstances()
    {
        $mw1 = new TestClassMiddleware();
        $mw2 = new TestMethodMiddleware();
        $attr = new Middleware($mw1, $mw2);
        $this->assertCount(2, $attr->instances);
        $this->assertSame($mw1, $attr->instances[0]);
        $this->assertSame($mw2, $attr->instances[1]);
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
