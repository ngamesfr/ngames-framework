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

namespace Ngames\Framework\Tests;

use Ngames\Framework\View;
use Ngames\Framework\Router\Route;
use Ngames\Framework\Exception;

class ViewTest extends \PHPUnit\Framework\TestCase
{
    public function testGetSetVariable()
    {
        $view = new View();
        $this->assertEquals('value', $view->test = 'value');
        $this->assertEquals('value', $view->test);
    }

    public function testGetVariable_errorNotSet()
    {
        $this->expectException('\Ngames\Framework\Exception');
        $this->expectExceptionMessage('Tried to access non existing variable test');
        $view = new View();
        $view->test;
    }

    public function testGetVariable_errorProtected()
    {
        $this->expectException('\Ngames\Framework\Exception');
        $this->expectExceptionMessage('Tried to access reserved variable __STYLESHEETS__');
        $view = new View();
        $view->__STYLESHEETS__;
    }

    public function testSetVariable_errorProtected()
    {
        $this->expectException('\Ngames\Framework\Exception');
        $this->expectExceptionMessage('Cannot used reserved variable __STYLESHEETS__');
        $view = new View();
        $view->__STYLESHEETS__ = 'value';
    }

    public function testUnsetVariable()
    {
        $view = new View();
        $view->test = 'value';
        unset($view->test);
        $this->assertObjectNotHasProperty('test', $view);
    }

    public function testGetScript()
    {
        $view = new View();
        $this->assertNull($view->getScript());
        $view = new View('script');
        $this->assertEquals('script', $view->getScript());
        $view = new View();
        $view->setScript('script2');
        $this->assertEquals('script2', $view->getScript());
    }

    public function testSetScriptFromRoute()
    {
        $route = new Route('module', 'controller', 'action');
        $view = new View();
        $view->setScriptFromRoute($route);
        $this->assertEquals('module/controller/action', $view->getScript());
    }

    public function testGetDirectory()
    {
        $view = new View();
        $this->assertEquals(ROOT_DIR . '/src/views/', $view->getDirectory());
        $view->setDirectory('directory');
        $this->assertEquals('directory', $view->getDirectory());
    }

    public function testGetParentView()
    {
        $view = new View();
        $this->assertNull($view->getParentView());
        $view->setParentView(new View('parent_script'));
        $this->assertNotNull($view->getParentView());
        $this->assertEquals('parent_script', $view->getParentView()
            ->getScript());
    }

    public function testPlaceholder()
    {
        $view = new View();
        $this->assertEmpty($view->placeholder('placeholder'));
        $view->placeholder('placeholder', 'placeholder_value');
        $this->assertEquals('placeholder_value', $view->placeholder('placeholder'));
    }

    public function testStartStopPlaceholder()
    {
        $view = new View();
        $view->startPlaceHolder('placeholder');
        echo 'placeholder_value';
        $view->stopPlaceHolder();
        $this->assertEquals('placeholder_value', $view->placeholder('placeholder'));
    }

    public function testStartPlaceholder_errorAlreadyStarted()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Cannot start a new placeholder: previous not stopped');
        $view = new View();
        $view->startPlaceHolder('placeholder');
        ob_end_clean();
        $view->startPlaceHolder('placeholder2');
    }

    public function testStopPlaceholder_errorNoneStarted()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Cannot stop a placeholder: none started');
        $view = new View();
        $view->stopPlaceHolder();
    }

    public function testStylesheets()
    {
        $view = new View();
        $view->appendStylesheet('stylesheet2');
        $view->prependStylesheet('stylesheet1');
        $view->appendStylesheet('stylesheet3');
        $this->assertEquals('<link rel="stylesheet" href="stylesheet1" /><link rel="stylesheet" href="stylesheet2" /><link rel="stylesheet" href="stylesheet3" />', $view->renderStylesheets());
    }

    public function testScripts()
    {
        $view = new View();
        $view->appendScript('script2');
        $view->prependScript('script1');
        $view->appendScript('script3');
        $this->assertEquals('<script src="script1"></script><script src="script2"></script><script src="script3"></script>', $view->renderScripts());
    }

    public function testSetLayout()
    {
        $view = new View();
        $view->setLayout('layout');
        $this->assertNotNull($view->getParentView());
        $this->assertEquals(ROOT_DIR . '/src/views/layouts/', $view->getParentView()->getDirectory());
        $this->assertEquals('layout', $view->getParentView()->getScript());
    }

    public function testDisableLayout()
    {
        $view = new View();
        $view->setLayout('layout');
        $this->assertNotNull($view->getParentView());
        $view->disableLayout();
        $this->assertNull($view->getParentView());
    }

    public function testRender()
    {
        $view = new View();
        $view->setDirectory(ROOT_DIR . '/tests/data/View/');
        $view->setScript('view');
        $view->setLayout('layout');
        $view->getParentView()->setDirectory(ROOT_DIR . '/tests/data/View/');
        $view->viewVariable = 'view_variable_value';
        $view->getParentView()->layoutVariable = 'layout_variable_value';

        $output = $view->render();
        $expectedOutput = "Content in layout\nContent in view\nview_variable_value\nlayout_variable_value\nview_variable_value";
        $this->assertEquals($expectedOutput, $output);
    }

    public function testRenderForModule()
    {
        $view = new View();
        $view->setDirectory(ROOT_DIR . '/tests/data/View/');
        $view->setScript('module');
        $view->setLayout('layout');
        $view->getParentView()->setDirectory(ROOT_DIR . '/tests/data/View/');
        $view->viewVariable = 'view_variable_value';
        $view->getParentView()->layoutVariable = 'layout_variable_value';

        $output = $view->render();
        $expectedOutput = "Content in layout\nContent in module\nview_variable_value\nlayout_variable_value\nview_variable_value";
        $this->assertEquals($expectedOutput, $output);
    }

    public function testRender_errorScriptNotFound()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('does_not_exist not found');
        $view = new View();
        $view->setDirectory('does_not_exist');
        $view->render();
    }

    public function testRender_errorPlaceholderNotStopped()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Exception caught during view rendering');
        $view = new View();
        $view->setDirectory(ROOT_DIR . '/tests/data/View/');
        $view->setScript('errorPlaceholderNotStopped');
        $view->render();
    }
}
