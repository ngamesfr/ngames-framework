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

/**
 * Represents the response from a controller.
 *
 */
class Response
{
    public const HTTP_STATUS_OK = 200;

    public const HTTP_STATUS_CREATED = 201;

    public const HTTP_STATUS_MOVED_PERMANENTLY = 301;

    public const HTTP_STATUS_FOUND = 302;

    public const HTTP_STATUS_NOT_MODIFIED = 304;

    public const HTTP_STATUS_BAD_REQUEST = 400;

    public const HTTP_STATUS_UNAUTHORIZED = 401;

    public const HTTP_STATUS_FORBIDDEN = 403;

    public const HTTP_STATUS_NOT_FOUND = 404;

    public const HTTP_STATUS_INTERNAL_SERVER_ERROR = 500;

    public const HTTP_STATUS_NOT_IMPLEMENTED = 501;

    public const CONTENT_TYPE_HEADER = 'Content-Type';

    public const ERROR_CONTENT_TYPE = 'text/plain; charset=utf-8';

    /**
     *
     * @var int
     */
    protected $statusCode;

    /**
     *
     * @var array
     */
    protected $headers;

    /**
     *
     * @var string|null
     */
    protected $content;

    /**
     * Initializes an empty successful response.
     */
    public function __construct()
    {
        $this->headers = [];
        $this->content = null;
        $this->statusCode = self::HTTP_STATUS_OK;
    }

    /**
     * Outputs the response.
     */
    public function send()
    {
        // Send headers
        foreach ($this->headers as $name => $value) {
            header($name . ': ' . $value);
        }

        // Set response code
        http_response_code($this->statusCode);

        // Send the content
        if ($this->content !== null) {
            echo $this->content;
        }
    }

    /**
     * @return array
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * Changes the response status code
     *
     * @param int $statusCode
     */
    public function setStatusCode($statusCode)
    {
        $this->statusCode = $statusCode;
    }

    /**
     * Adds a new header value
     *
     * @param string $name
     * @param string $value
     */
    public function setHeader($name, $value)
    {
        $this->headers[$name] = $value;
    }

    /**
     * Sets the content type of the response (helper to set a header).
     *
     * @param String $contentType
     * @param String|null $charset
     *            Optional charset
     */
    public function setContentType($contentType, $charset = null)
    {
        $headerValue = $contentType;

        if ($charset !== null) {
            $headerValue .= '; charset=' . $charset;
        }

        $this->setHeader(self::CONTENT_TYPE_HEADER, $headerValue);
    }

    /**
     * Sets the content of the response
     *
     * @param string|null $content
     */
    public function setContent($content)
    {
        $this->content = $content;
    }

    /**
     * Create a successful response
     *
     * @param string|null $content
     * @return Response
     */
    public static function createOkResponse($content = null)
    {
        $response = new self();
        $response->setContent($content);
        $response->setStatusCode(self::HTTP_STATUS_OK);

        return $response;
    }

    /**
     * Create an internal error response
     *
     * @param string|null $message
     * @return Response
     */
    public static function createInternalErrorResponse($message = null)
    {
        $response = new self();
        $response->setHeader(self::CONTENT_TYPE_HEADER, self::ERROR_CONTENT_TYPE);
        $response->setContent($message !== null ? $message : 'Internal server error.');
        $response->setStatusCode(self::HTTP_STATUS_INTERNAL_SERVER_ERROR);

        return $response;
    }

    /**
     * Create an unauthorized response
     *
     * @param string|null $message
     * @return Response
     */
    public static function createUnauthorizedResponse($message = null)
    {
        $response = new self();
        $response->setHeader(self::CONTENT_TYPE_HEADER, self::ERROR_CONTENT_TYPE);
        $response->setContent($message !== null ? $message : 'Unauthorized.');
        $response->setStatusCode(self::HTTP_STATUS_UNAUTHORIZED);

        return $response;
    }

    /**
     * Create a not found response
     *
     * @param string|null $message
     * @return Response
     */
    public static function createNotFoundResponse($message = null)
    {
        $response = new self();
        $response->setHeader(self::CONTENT_TYPE_HEADER, self::ERROR_CONTENT_TYPE);
        $response->setContent($message !== null ? $message : 'File not found.');
        $response->setStatusCode(self::HTTP_STATUS_NOT_FOUND);

        return $response;
    }

    /**
     * Create a bad request response
     *
     * @param string|null $message
     * @return Response
     */
    public static function createBadRequestResponse($message = null)
    {
        $response = new self();
        $response->setHeader(self::CONTENT_TYPE_HEADER, self::ERROR_CONTENT_TYPE);
        $response->setContent($message !== null ? $message : 'Bad request.');
        $response->setStatusCode(self::HTTP_STATUS_BAD_REQUEST);

        return $response;
    }

    /**
     * Create a redirect response
     *
     * @param string $url
     * @return Response
     */
    public static function createRedirectResponse($url)
    {
        $response = new self();
        $response->setStatusCode(self::HTTP_STATUS_MOVED_PERMANENTLY);
        $response->setHeader('Location', $url);

        return $response;
    }
}
