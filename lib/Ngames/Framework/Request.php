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

namespace Ngames\Framework;

use Ngames\Framework\Storage\PhpSession;

/**
 * Stores all informations relative to the current request being processed.
 * This is initialized by the application, and given to the controller.
 *
 */
class Request
{
    public const HTTP_METHOD_OPTIONS = 'OPTIONS';

    public const HTTP_METHOD_GET = 'GET';

    public const HTTP_METHOD_HEAD = 'HEAD';

    public const HTTP_METHOD_POST = 'POST';

    public const HTTP_METHOD_PUT = 'PUT';

    public const HTTP_METHOD_DELETE = 'DELETE';

    public const HTTP_METHOD_TRACE = 'TRACE';

    public const HTTP_METHOD_CONNECT = 'CONNECT';

    /**
     * The request method.
     * Can be only one of the constants HTTP_METHOD*.
     *
     * @var string
     */
    protected $method;

    /**
     * The requested URI.
     * Does not contain protocol, hostname nor query string.
     *
     * @var string
     */
    protected $requestUri;

    /**
     * URL Parameters of the request.
     *
     * @var array
     */
    protected $getParameters;

    /**
     * POST parameters (form data).
     *
     * @var array
     */
    protected $postParameters;

    /**
     * Cookies from the request (at request start, ie changes during request processing are not reflected here).
     *
     * @var array
     */
    protected $cookies;

    /**
     * Server variables.
     *
     * @var array
     */
    protected $server;

    /**
     * Session variables at request start (as for cookies, changes during request processing are not reflected here).
     *
     * @var PhpSession
     */
    protected $session;

    /**
     * Files sent in current request.
     *
     * @var array
     */
    protected $files;

    /**
     * The raw body as string
     *
     * @var string
     */
    protected $rawBody;

    /**
     * Create a new request object from the request context
     *
     * @param array $getParameters
     * @param array $postParameters
     * @param array $cookies
     * @param array $server
     * @param array $files
     */
    public function __construct($getParameters = [], $postParameters = [], $cookies = [], $server = [], $files = [], $rawBody = null)
    {
        $this->getParameters = $getParameters;
        $this->postParameters = $postParameters;
        $this->cookies = $cookies;
        $this->session = PhpSession::getInstance();
        $this->server = $server;
        $this->files = $files;
        $this->rawBody = $rawBody;

        if (!$this->isCli()) {
            $this->method = $this->server['REQUEST_METHOD'];
            $this->requestUri = $this->extractUri($this->server['REQUEST_URI']);
        }
    }

    /**
     *
     * @return PhpSession
     */
    public function getSession()
    {
        return $this->session;
    }

    /**
     *
     * @return string
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * Return the value of the GET parameter.
     * If not found, return $default instead
     *
     * @param string $name
     * @param string|null $default
     * @return string
     */
    public function getGetParameter($name, $default = null)
    {
        return array_key_exists($name, $this->getParameters) ? $this->getParameters[$name] : $default;
    }

    /**
     * Return the value of the POST parameter.
     * If not found, return $default instead
     *
     * @param string $name
     * @param string|null $default
     * @return string
     */
    public function getPostParameter($name, $default = null)
    {
        return array_key_exists($name, $this->postParameters) ? $this->postParameters[$name] : $default;
    }

    /**
     * Return the raw body as string.
     *
     * @return string
     */
    public function getRawBody()
    {
        return $this->rawBody;
    }

    /**
     * Override the raw body. Useful for testing.
     */
    public function setRawBody(string $body): void
    {
        $this->rawBody = $body;
    }

    /**
     * Return the request body decoded as JSON (associative array or scalar).
     * Returns null if the body is empty or is not valid JSON.
     *
     * @return mixed|null
     */
    public function getJsonBody()
    {
        if ($this->rawBody === null || $this->rawBody === '') {
            return null;
        }

        try {
            return json_decode($this->rawBody, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            return null;
        }
    }

    /**
     * Return the value of the cookie.
     * If not found, return $default instead
     *
     * @param string $name
     * @param string|null $default
     * @return string
     */
    public function getCookie($name, $default = null)
    {
        return array_key_exists($name, $this->cookies) ? $this->cookies[$name] : $default;
    }

    /**
     * Return the value of the header.
     * If not found, return $default instead
     *
     * @param string $name
     * @param string|null $default
     * @return string
     */
    public function getHeader($name, $default = null)
    {
        $serverKey = 'HTTP_' . str_replace('-', '_', mb_strtoupper($name));
        return array_key_exists($serverKey, $this->server) ? $this->server[$serverKey] : $default;
    }

    /**
     * Return the uploaded file by name
     *
     * @param string $name
     * @return array|null
     */
    public function getFile($name)
    {
        return array_key_exists($name, $this->files) ? $this->files[$name] : null;
    }

    /**
     * Merge additional parameters into the GET parameters.
     *
     * @param array $parameters
     * @return void
     */
    public function mergeGetParameters(array $parameters)
    {
        $this->getParameters = array_merge($this->getParameters, $parameters);
    }

    /**
     * Whether the application is being run in command line or not
     *
     * @return boolean
     */
    public function isCli()
    {
        return PHP_SAPI === 'cli';
    }

    /**
     *
     * @return boolean
     */
    public function isPost()
    {
        return $this->method == self::HTTP_METHOD_POST;
    }

    /**
     *
     * @return boolean
     */
    public function isGet()
    {
        return $this->method == self::HTTP_METHOD_GET;
    }

    /**
     *
     * @return boolean
     */
    public function isDelete()
    {
        return $this->method == self::HTTP_METHOD_DELETE;
    }

    /**
     *
     * @return boolean
     */
    public function isPut()
    {
        return $this->method == self::HTTP_METHOD_PUT;
    }

    /**
     *
     * @return string
     */
    public function getRequestUri()
    {
        return $this->requestUri;
    }

    /**
     * Set the request URI.
     * Useful to change it in forward use-case.
     *
     * @param String $uri
     * @return Request
     */
    public function setRequestUri($uri)
    {
        $this->requestUri = $uri;

        return $this;
    }

    /**
     * Return the client IP.
     * Tries to return the most relevant value.
     *
     * @return string|null
     */
    public function getRemoteAddress()
    {
        $result = null;

        if (!empty($this->server['HTTP_X_FORWARDED_FOR'])) {
            $result = $this->server['HTTP_X_FORWARDED_FOR'];
        } elseif (!empty($this->server['HTTP_CLIENT_IP'])) {
            $result = $this->server['HTTP_CLIENT_IP'];
        } elseif (!empty($this->server['REMOTE_ADDR'])) {
            $result = $this->server['REMOTE_ADDR'];
        }

        return $result;
    }

    /**
     * Ensures the string matches a URI, otherwise return null
     *
     * @return string
     * @throws Exception
     */
    private function extractUri($requestUriHeader)
    {
        $matches = [];

        if (preg_match('/([a-z0-9_\-\/]+)/', mb_strtolower($requestUriHeader), $matches) && mb_strlen($matches[0]) > 0) {
            $result = $matches[0];
        } else {
            throw new Exception('Invalid requested URI');
        }

        return $result;
    }
}
