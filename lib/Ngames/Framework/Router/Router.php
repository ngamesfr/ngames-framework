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

namespace Ngames\Framework\Router;

/**
 * The router class is used to find the route for a given URI.
 * You can add matchers to it to register your own routes.
 *
 */
class Router
{
    /**
     *
     * @var Matcher[]
     */
    private $matchers = [];

    /**
     * Adds a new matcher at the begining of the matcher list.
     *
     * @param Matcher $matcher
     *
     * @return Router
     */
    public function addMatcher(Matcher $matcher)
    {
        $this->matchers[] = $matcher;

        return $this;
    }

    /**
     *
     * @param string $uri
     * @return Route|null the found route if any, null otherwise
     */
    public function getRoute($uri)
    {
        $result = null;

        foreach ($this->matchers as $matcher) {
            $matchedRoute = $matcher->match($uri);
            if ($matchedRoute !== null) {
                $result = $matchedRoute;
                break;
            }
        }

        return $result;
    }
}
