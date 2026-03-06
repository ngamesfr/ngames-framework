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

namespace Ngames\Framework\Tests\Router;

use Ngames\Framework\Router\Matcher;
use Ngames\Framework\Router\Router;

class RouterTest extends \PHPUnit\Framework\TestCase
{
    public function testGetRoute_noRouteDefined()
    {
        $router = new Router();
        $route = $router->getRoute('/');
        $this->assertNull($route);
    }

    public function testGetRoute_match()
    {
        $router = new Router();
        $router->addMatcher(@Matcher::forConventionRoute('/', 'default-module', 'default-controller', 'default-action'));
        $route = $router->getRoute('/');
        $this->assertEquals('default-module', $route->getModuleName());
        $this->assertEquals('default-controller', $route->getControllerName());
        $this->assertEquals('default-action', $route->getActionName());
    }

    public function testGetRoute_noMatch()
    {
        $router = new Router();
        $router->addMatcher(@Matcher::forConventionRoute('/test', 'default-module', 'default-controller', 'default-action'));
        $route = $router->getRoute('/test1');
        $this->assertNull($route);
    }

    public function testGetRoute_routeOrder()
    {
        $router = new Router();
        $router->addMatcher(@Matcher::forConventionRoute('/test', 'test1-module', 'test1-controller', 'test1-action'));
        $router->addMatcher(@Matcher::forConventionRoute('/test', 'test2-module', 'test2-controller', 'test2-action'));
        $route = $router->getRoute('/test');
        $this->assertEquals('test1-module', $route->getModuleName());
        $this->assertEquals('test1-controller', $route->getControllerName());
        $this->assertEquals('test1-action', $route->getActionName());
    }

    public function testUrl_namedRoute()
    {
        $router = new Router();
        $router->addMatcher(@Matcher::forConventionRoute('/article/:id', 'app', 'article', 'show', 'article'));
        $this->assertEquals('/article/42', $router->url('article', ['id' => '42']));
    }

    public function testUrl_namedRouteNoParams()
    {
        $router = new Router();
        $router->addMatcher(@Matcher::forConventionRoute('/blog', 'app', 'news', 'index', 'blog'));
        $this->assertEquals('/blog', $router->url('blog'));
    }

    public function testUrl_unknownName()
    {
        $this->expectException(\InvalidArgumentException::class);
        $router = new Router();
        $router->url('unknown');
    }

    public function testGetRoute_aliasBeforeDefault()
    {
        $router = new Router();
        $router->addMatcher(@Matcher::forConventionRoute('/blog', 'app', 'news', 'index', 'blog'));
        $router->addMatcher(@Matcher::forConventionRoute('/:module/:controller/:action'));
        $route = $router->getRoute('/blog');
        $this->assertEquals('app', $route->getModuleName());
        $this->assertEquals('news', $route->getControllerName());
        $this->assertEquals('index', $route->getActionName());
    }

    // HTTP method routing tests
    public function testGetRoute_withMethodPicksCorrectMatcher()
    {
        $router = new Router();
        $router->addMatcher(@Matcher::forConventionRoute('/test', 'mod', 'ctrl', 'get-action', null, 'GET'));
        $router->addMatcher(@Matcher::forConventionRoute('/test', 'mod', 'ctrl', 'delete-action', null, 'DELETE'));

        $getRoute = $router->getRoute('/test', 'GET');
        $this->assertNotNull($getRoute);
        $this->assertEquals('get-action', $getRoute->getActionName());

        $deleteRoute = $router->getRoute('/test', 'DELETE');
        $this->assertNotNull($deleteRoute);
        $this->assertEquals('delete-action', $deleteRoute->getActionName());
    }

    public function testGetRoute_withoutMethodStillWorks()
    {
        $router = new Router();
        $router->addMatcher(@Matcher::forConventionRoute('/test', 'mod', 'ctrl', 'act'));
        $route = $router->getRoute('/test');
        $this->assertNotNull($route);
        $this->assertEquals('act', $route->getActionName());
    }

    public function testGetRoute_methodConstraintNoMatch()
    {
        $router = new Router();
        $router->addMatcher(@Matcher::forConventionRoute('/test', 'mod', 'ctrl', 'act', null, 'GET'));
        $route = $router->getRoute('/test', 'POST');
        $this->assertNull($route);
    }

    // Annotated route tests
    public function testGetRoute_annotatedRoute()
    {
        $router = new Router();
        $router->addMatcher(new Matcher('/api/users/:id', 'GET', 'App\\UserCtrl', 'show'));
        $route = $router->getRoute('/api/users/42', 'GET');
        $this->assertNotNull($route);
        $this->assertTrue($route->isAnnotated());
        $this->assertEquals('App\\UserCtrl', $route->getControllerClass());
    }

    public function testUrl_annotatedNamedRoute()
    {
        $router = new Router();
        $router->addMatcher(new Matcher('/api/users/:id', 'GET', 'App\\UserCtrl', 'show', [], 'user.show'));
        $this->assertEquals('/api/users/42', $router->url('user.show', ['id' => '42']));
    }
}
