<?php

namespace Ngames\Framework\Tests\Router\Attribute;

use Ngames\Framework\Router\Attribute\Delete;
use Ngames\Framework\Router\Attribute\Get;
use Ngames\Framework\Router\Attribute\Post;
use Ngames\Framework\Router\Attribute\Put;
use PHPUnit\Framework\TestCase;

class HttpMethodAttributeTest extends TestCase
{
    public function testGetStoresPath()
    {
        $attr = new Get('/:id');
        $this->assertEquals('/:id', $attr->path);
    }

    public function testGetDefaultEmptyPath()
    {
        $attr = new Get();
        $this->assertEquals('', $attr->path);
    }

    public function testPostStoresPath()
    {
        $attr = new Post('/create');
        $this->assertEquals('/create', $attr->path);
    }

    public function testPutStoresPath()
    {
        $attr = new Put('/:id');
        $this->assertEquals('/:id', $attr->path);
    }

    public function testDeleteStoresPath()
    {
        $attr = new Delete('/:id');
        $this->assertEquals('/:id', $attr->path);
    }

    public function testGetIsMethodLevelAttribute()
    {
        $ref = new \ReflectionClass(Get::class);
        $attrs = $ref->getAttributes(\Attribute::class);
        $this->assertCount(1, $attrs);
        $attr = $attrs[0]->newInstance();
        $this->assertEquals(\Attribute::TARGET_METHOD, $attr->flags);
    }

    public function testPostIsMethodLevelAttribute()
    {
        $ref = new \ReflectionClass(Post::class);
        $attrs = $ref->getAttributes(\Attribute::class);
        $attr = $attrs[0]->newInstance();
        $this->assertEquals(\Attribute::TARGET_METHOD, $attr->flags);
    }

    public function testPutIsMethodLevelAttribute()
    {
        $ref = new \ReflectionClass(Put::class);
        $attrs = $ref->getAttributes(\Attribute::class);
        $attr = $attrs[0]->newInstance();
        $this->assertEquals(\Attribute::TARGET_METHOD, $attr->flags);
    }

    public function testDeleteIsMethodLevelAttribute()
    {
        $ref = new \ReflectionClass(Delete::class);
        $attrs = $ref->getAttributes(\Attribute::class);
        $attr = $attrs[0]->newInstance();
        $this->assertEquals(\Attribute::TARGET_METHOD, $attr->flags);
    }
}
