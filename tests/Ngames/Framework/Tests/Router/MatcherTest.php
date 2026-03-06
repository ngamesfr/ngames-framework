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

class MatcherTest extends \PHPUnit\Framework\TestCase
{
    // Annotated route tests (primary API)
    public function testAnnotatedRoute_matchesPattern()
    {
        $matcher = new Matcher('/api/users/:id', 'GET', 'App\\UserController', 'show');
        $result = $matcher->match('/api/users/42', 'GET');
        $this->assertNotNull($result);
        $this->assertTrue($result->isAnnotated());
        $this->assertEquals('App\\UserController', $result->getControllerClass());
        $this->assertEquals('show', $result->getActionMethod());
        $this->assertEquals(['id' => '42'], $result->getParameters());
    }

    public function testAnnotatedRoute_noMatch()
    {
        $matcher = new Matcher('/api/users/:id', 'GET', 'App\\UserController', 'show');
        $this->assertNull($matcher->match('/api/posts/42', 'GET'));
    }

    public function testAnnotatedRoute_methodConstraint()
    {
        $matcher = new Matcher('/api/users', 'POST', 'App\\UserController', 'create');
        $this->assertNull($matcher->match('/api/users', 'GET'));
        $this->assertNotNull($matcher->match('/api/users', 'POST'));
    }

    public function testAnnotatedRoute_methodIsCaseInsensitive()
    {
        $matcher = new Matcher('/test', 'get', 'App\\Ctrl', 'act');
        $this->assertNotNull($matcher->match('/test', 'GET'));
        $this->assertNotNull($matcher->match('/test', 'get'));
    }

    public function testAnnotatedRoute_middlewaresPassedToRoute()
    {
        $matcher = new Matcher('/test', 'GET', 'App\\Ctrl', 'act', ['App\\AuthMiddleware']);
        $result = $matcher->match('/test', 'GET');
        $this->assertEquals(['App\\AuthMiddleware'], $result->getMiddlewares());
    }

    public function testAnnotatedRoute_getName()
    {
        $matcher = new Matcher('/blog', 'GET', 'App\\BlogCtrl', 'index', [], 'blog');
        $this->assertEquals('blog', $matcher->getName());
    }

    public function testAnnotatedRoute_getPattern()
    {
        $matcher = new Matcher('/blog', 'GET', 'App\\BlogCtrl', 'index');
        $this->assertEquals('/blog', $matcher->getPattern());
    }

    public function testAnnotatedRoute_getMethod()
    {
        $matcher = new Matcher('/test', 'POST', 'App\\Ctrl', 'act');
        $this->assertEquals('POST', $matcher->getMethod());
    }

    // Legacy convention-based route tests
    public function testLegacy_invalidInitialization_missingModuleKeyAndValue()
    {
        $this->expectException('\Ngames\Framework\Router\InvalidMatcherException');
        $this->expectExceptionMessage('Missing module key or module value, or provided both');
        @Matcher::forConventionRoute('/:controller/:action');
    }

    public function testLegacy_invalidInitialization_moduleKeyAndValue()
    {
        $this->expectException('\Ngames\Framework\Router\InvalidMatcherException');
        $this->expectExceptionMessage('Missing module key or module value, or provided both');
        @Matcher::forConventionRoute('/:module', 'module');
    }

    public function testLegacy_invalidInitialization_missingControllerKeyAndValue()
    {
        $this->expectException('\Ngames\Framework\Router\InvalidMatcherException');
        $this->expectExceptionMessage('Missing controller key or controller value, or provided both');
        @Matcher::forConventionRoute('/:module/:action');
    }

    public function testLegacy_invalidInitialization_controllerKeyAndValue()
    {
        $this->expectException('\Ngames\Framework\Router\InvalidMatcherException');
        $this->expectExceptionMessage('Missing controller key or controller value, or provided both');
        @Matcher::forConventionRoute('/:controller', 'module', 'controller');
    }

    public function testLegacy_invalidInitialization_missingActionKeyAndValue()
    {
        $this->expectException('\Ngames\Framework\Router\InvalidMatcherException');
        $this->expectExceptionMessage('Missing action key or action value, or provided both');
        @Matcher::forConventionRoute('/:module/:controller');
    }

    public function testLegacy_invalidInitialization_actionKeyAndValue()
    {
        $this->expectException('\Ngames\Framework\Router\InvalidMatcherException');
        $this->expectExceptionMessage('Missing action key or action value, or provided both');
        @Matcher::forConventionRoute('/:action', 'module', 'controller', 'action');
    }

    public function testLegacy_noMatch()
    {
        $matcher1 = @Matcher::forConventionRoute('/test', 'module1', 'controller1', 'action1');
        $this->assertNull($matcher1->match('/test1'));

        $matcher2 = @Matcher::forConventionRoute('/test/test', 'module2', 'controller2', 'action2');
        $this->assertNull($matcher1->match('/test/test1'));

        $matcher2 = @Matcher::forConventionRoute('/:module/:controller/:action');
        $this->assertNull($matcher1->match('/module/controller/action/a'));
    }

    public function testLegacy_match_onlyDefault()
    {
        $matcher1 = @Matcher::forConventionRoute('/test', 'module1', 'controller1', 'action1');
        $result1 = $matcher1->match('/test');
        $this->assertNotNull($result1);
        $this->assertEquals('module1', $result1->getModuleName());
        $this->assertEquals('controller1', $result1->getControllerName());
        $this->assertEquals('action1', $result1->getActionName());
    }

    public function testLegacy_match_matchModule()
    {
        $matcher = @Matcher::forConventionRoute('/:module', null, 'controller', 'action');
        $result = $matcher->match('/module-match');
        $this->assertNotNull($result);
        $this->assertEquals('module-match', $result->getModuleName());
        $this->assertEquals('controller', $result->getControllerName());
        $this->assertEquals('action', $result->getActionName());
    }

    public function testLegacy_match_matchController()
    {
        $matcher = @Matcher::forConventionRoute('/:controller', 'module', null, 'action');
        $result = $matcher->match('/controller-match');
        $this->assertNotNull($result);
        $this->assertEquals('module', $result->getModuleName());
        $this->assertEquals('controller-match', $result->getControllerName());
        $this->assertEquals('action', $result->getActionName());
    }

    public function testLegacy_match_matchAction()
    {
        $matcher = @Matcher::forConventionRoute('/:action', 'module', 'controller', null);
        $result = $matcher->match('/action-match');
        $this->assertNotNull($result);
        $this->assertEquals('module', $result->getModuleName());
        $this->assertEquals('controller', $result->getControllerName());
        $this->assertEquals('action-match', $result->getActionName());
    }

    public function testLegacy_match_staticAlias()
    {
        $matcher = @Matcher::forConventionRoute('/blog', 'application', 'news', 'index');
        $result = $matcher->match('/blog');
        $this->assertNotNull($result);
        $this->assertEquals('application', $result->getModuleName());
        $this->assertEquals('news', $result->getControllerName());
        $this->assertEquals('index', $result->getActionName());
        $this->assertEmpty($result->getParameters());
    }

    public function testLegacy_match_customParameter()
    {
        $matcher = @Matcher::forConventionRoute('/article/:id', 'application', 'article', 'show');
        $result = $matcher->match('/article/42');
        $this->assertNotNull($result);
        $this->assertEquals('application', $result->getModuleName());
        $this->assertEquals('article', $result->getControllerName());
        $this->assertEquals('show', $result->getActionName());
        $this->assertEquals(['id' => '42'], $result->getParameters());
        $this->assertEquals('42', $result->getParameter('id'));
        $this->assertNull($result->getParameter('unknown'));
        $this->assertEquals('default', $result->getParameter('unknown', 'default'));
    }

    public function testLegacy_match_multipleCustomParameters()
    {
        $matcher = @Matcher::forConventionRoute('/article/:id/:slug', 'application', 'article', 'show');
        $result = $matcher->match('/article/42/my-title');
        $this->assertNotNull($result);
        $this->assertEquals(['id' => '42', 'slug' => 'my-title'], $result->getParameters());
    }

    public function testLegacy_match_customParameterNoMatch()
    {
        $matcher = @Matcher::forConventionRoute('/article/:id', 'application', 'article', 'show');
        $this->assertNull($matcher->match('/blog/42'));
    }

    public function testLegacy_match_defaultPatternStillWorks()
    {
        $matcher = @Matcher::forConventionRoute('/:module/:controller/:action');
        $result = $matcher->match('/app/home/index');
        $this->assertNotNull($result);
        $this->assertEquals('app', $result->getModuleName());
        $this->assertEquals('home', $result->getControllerName());
        $this->assertEquals('index', $result->getActionName());
        $this->assertEmpty($result->getParameters());
    }

    public function testLegacy_getName()
    {
        $matcher = @Matcher::forConventionRoute('/blog', 'app', 'news', 'index', 'blog');
        $this->assertEquals('blog', $matcher->getName());
    }

    public function testLegacy_getName_null()
    {
        $matcher = @Matcher::forConventionRoute('/blog', 'app', 'news', 'index');
        $this->assertNull($matcher->getName());
    }

    public function testLegacy_matchWithMethodGet_matchesGet()
    {
        $matcher = @Matcher::forConventionRoute('/test', 'mod', 'ctrl', 'act', null, 'GET');
        $result = $matcher->match('/test', 'GET');
        $this->assertNotNull($result);
    }

    public function testLegacy_matchWithMethodGet_rejectsPost()
    {
        $matcher = @Matcher::forConventionRoute('/test', 'mod', 'ctrl', 'act', null, 'GET');
        $result = $matcher->match('/test', 'POST');
        $this->assertNull($result);
    }

    public function testLegacy_matchWithMethodNull_matchesAnyMethod()
    {
        $matcher = @Matcher::forConventionRoute('/test', 'mod', 'ctrl', 'act');
        $this->assertNotNull($matcher->match('/test', 'GET'));
        $this->assertNotNull($matcher->match('/test', 'POST'));
        $this->assertNotNull($matcher->match('/test', 'DELETE'));
        $this->assertNotNull($matcher->match('/test'));
    }

    public function testLegacy_matchWithMethodIsCaseInsensitive()
    {
        $matcher = @Matcher::forConventionRoute('/test', 'mod', 'ctrl', 'act', null, 'get');
        $this->assertNotNull($matcher->match('/test', 'GET'));
        $this->assertNotNull($matcher->match('/test', 'get'));
    }

    public function testLegacy_getMethod()
    {
        $matcher = @Matcher::forConventionRoute('/test', 'mod', 'ctrl', 'act', null, 'POST');
        $this->assertEquals('POST', $matcher->getMethod());
    }

    public function testLegacy_getMethod_null()
    {
        $matcher = @Matcher::forConventionRoute('/test', 'mod', 'ctrl', 'act');
        $this->assertNull($matcher->getMethod());
    }

    // Deprecation notice test
    public function testLegacy_triggersDeprecationNotice()
    {
        $deprecationTriggered = false;
        set_error_handler(function ($errno) use (&$deprecationTriggered) {
            if ($errno === E_USER_DEPRECATED) {
                $deprecationTriggered = true;
            }
            return true;
        });

        Matcher::forConventionRoute('/test', 'mod', 'ctrl', 'act');

        restore_error_handler();
        $this->assertTrue($deprecationTriggered);
    }
}
