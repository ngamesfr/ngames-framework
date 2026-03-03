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

use Ngames\Framework\Response;

class ResponseTest extends \PHPUnit\Framework\TestCase
{
    public function testSend_default()
    {
        $response = new Response();
        $output = $this->sendResponseAndReturnOutput($response);
        $this->assertEquals(200, http_response_code());
        $this->assertEmpty($output);
    }

    public function testSend_withContent()
    {
        $response = new Response();
        $response->setContentType('text/html', 'UTF-8');
        $response->setContent('content');
        $output = $this->sendResponseAndReturnOutput($response);
        $this->assertEquals(200, http_response_code());
        $this->assertContains('Content-Type: text/html; charset=UTF-8', \xdebug_get_headers());
        $this->assertEquals('content', $output);
    }

    public function testSend_statusCode()
    {
        $response = new Response();
        $response->setStatusCode(1234);
        $response->setContentType('text/html', 'UTF-8');
        $output = $this->sendResponseAndReturnOutput($response);
        $this->assertEquals(1234, http_response_code());
        $this->assertContains('Content-Type: text/html; charset=UTF-8', \xdebug_get_headers());
        $this->assertEmpty($output);
    }


    public function testSend_contentType()
    {
        $response = new Response();
        $response->setContentType('contentType', 'charset');
        $output = $this->sendResponseAndReturnOutput($response);
        $this->assertEquals(200, http_response_code());
        $this->assertContains('Content-Type: contentType; charset=charset', \xdebug_get_headers());
        $this->assertEmpty($output);
    }

    public function testCreateOkResponse()
    {
        $response = Response::createOkResponse('content');
        $response->setContentType('text/html', 'UTF-8');
        $output = $this->sendResponseAndReturnOutput($response);
        $this->assertEquals(200, http_response_code());
        $this->assertContains('Content-Type: text/html; charset=UTF-8', \xdebug_get_headers());
        $this->assertEquals('content', $output);
    }


    public function testCreateInternalErrorResponse()
    {
        $response = Response::createInternalErrorResponse('content');
        $output = $this->sendResponseAndReturnOutput($response);
        $this->assertEquals(500, http_response_code());
        $this->assertContains('Content-Type: text/plain; charset=utf-8', \xdebug_get_headers());
        $this->assertEquals('content', $output);
    }


    public function testCreateNotFoundResponse()
    {
        $response = Response::createNotFoundResponse('content');
        $output = $this->sendResponseAndReturnOutput($response);
        $this->assertEquals(404, http_response_code());
        $this->assertContains('Content-Type: text/plain; charset=utf-8', \xdebug_get_headers());
        $this->assertEquals('content', $output);
    }


    public function testCreateBadRequestResponse()
    {
        $response = Response::createBadRequestResponse('content');
        $output = $this->sendResponseAndReturnOutput($response);
        $this->assertEquals(400, http_response_code());
        $this->assertContains('Content-Type: text/plain; charset=utf-8', \xdebug_get_headers());
        $this->assertEquals('content', $output);
    }


    public function testCreateUnauthorizedResponse()
    {
        $response = Response::createUnauthorizedResponse('content');
        $output = $this->sendResponseAndReturnOutput($response);
        $this->assertEquals(401, http_response_code());
        $this->assertContains('Content-Type: text/plain; charset=utf-8', \xdebug_get_headers());
        $this->assertEquals('content', $output);
    }


    public function testCreateRedirectResponse()
    {
        $response = Response::createRedirectResponse('newUrl');
        $output = $this->sendResponseAndReturnOutput($response);
        $this->assertEquals(301, http_response_code());
        $this->assertContains('Location: newUrl', \xdebug_get_headers());
        $this->assertEmpty($output);
    }

    /**
     * Sends the response and return the output in a string
     *
     * @param Response $response
     */
    private function sendResponseAndReturnOutput(Response $response)
    {
        $result = null;

        ob_start();
        $response->send();
        $result = ob_get_contents();
        ob_end_clean();

        return $result;
    }
}
