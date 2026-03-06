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
 * Matcher class used to identify matching route for a given requested URI
 *
 */
class Matcher
{
    public const MODULE_KEY = ':module';

    public const CONTROLLER_KEY = ':controller';

    public const ACTION_KEY = ':action';

    private $pattern;

    private $moduleName;

    private $controllerName;

    private $actionName;

    private $name;

    private $method;

    private $controllerClass;

    private $actionMethod;

    private $middlewares;

    /**
     * Create a new matcher that will be used to test the route eligility.
     *
     * The pattern may define the URI part where module, controller or action are read. If not, the corresponding element must have a value defined.
     *
     * Samples pattern are:
     * /home + module=default, controller=index, action=index
     * /:controller/:action + module=default
     * Etc.
     *
     * @param string $pattern
     * @param string|null $moduleName
     * @param string|null $controllerName
     * @param string|null $actionName
     * @param string|null $name
     * @param string|null $method
     */
    public function __construct(
        $pattern,
        $moduleName = null,
        $controllerName = null,
        $actionName = null,
        $name = null,
        $method = null
    ) {
        $this->pattern = $pattern;
        $this->moduleName = $moduleName;
        $this->controllerName = $controllerName;
        $this->actionName = $actionName;
        $this->name = $name;
        $this->method = $method !== null ? strtoupper($method) : null;
        $this->middlewares = [];
        $this->check();
    }

    /**
     * Create a matcher for an annotated route (attribute-based routing).
     */
    public static function forAnnotatedRoute(
        string $pattern,
        string $httpMethod,
        string $controllerClass,
        string $actionMethod,
        array $middlewares = []
    ): self {
        $matcher = new \ReflectionClass(self::class);
        $instance = $matcher->newInstanceWithoutConstructor();
        $instance->pattern = $pattern;
        $instance->method = strtoupper($httpMethod);
        $instance->controllerClass = $controllerClass;
        $instance->actionMethod = $actionMethod;
        $instance->middlewares = $middlewares;
        return $instance;
    }

    /**
     * @return string|null
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getPattern()
    {
        return $this->pattern;
    }

    /**
     * @return string|null
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * Tries to match the input URI.
     * Output is null if no match, a route otherwise.
     *
     * @param string $uri
     * @param string|null $method
     *
     * @return Route|null
     */
    public function match($uri, $method = null)
    {
        // Check HTTP method constraint
        if ($this->method !== null && $method !== null && strtoupper($method) !== $this->method) {
            return null;
        }

        $result = $this->matchPattern($uri);

        if ($result === null) {
            return null;
        }

        return new Route(
            $result['moduleName'],
            $result['controllerName'],
            $result['actionName'],
            $result['parameters'],
            $this->controllerClass,
            $this->actionMethod,
            $this->middlewares ?? []
        );
    }

    /**
     * Match the URI against the pattern and extract route components.
     *
     * @param string $uri
     * @return array|null
     */
    private function matchPattern($uri)
    {
        $preparedPattern = $this->prepareForMatching($this->pattern);
        $preparedUri = $this->prepareForMatching($uri);

        if (count($preparedPattern) !== count($preparedUri)) {
            return null;
        }

        $moduleName = $this->moduleName;
        $controllerName = $this->controllerName;
        $actionName = $this->actionName;
        $parameters = [];

        for ($i = 0; $i < count($preparedPattern); $i++) {
            $patternPart = $preparedPattern[$i];
            $uriPart = $preparedUri[$i];

            if ($patternPart === $uriPart) {
                continue;
            }

            if ($patternPart === self::MODULE_KEY) {
                $moduleName = $uriPart;
            } elseif ($patternPart === self::CONTROLLER_KEY) {
                $controllerName = $uriPart;
            } elseif ($patternPart === self::ACTION_KEY) {
                $actionName = $uriPart;
            } elseif (str_starts_with($patternPart, ':')) {
                $parameters[substr($patternPart, 1)] = $uriPart;
            } else {
                return null;
            }
        }

        return [
            'moduleName' => $moduleName,
            'controllerName' => $controllerName,
            'actionName' => $actionName,
            'parameters' => $parameters,
        ];
    }

    /**
     * Checks that the configuration of the matcher is valid and throws an exception otherwise.
     *
     * @throws InvalidMatcherException
     */
    private function check()
    {
        if (!($this->moduleName !== null xor strpos($this->pattern, self::MODULE_KEY) !== false)) {
            throw new InvalidMatcherException('Missing module key or module value, or provided both');
        }
        if (!($this->controllerName !== null xor strpos($this->pattern, self::CONTROLLER_KEY) !== false)) {
            throw new InvalidMatcherException('Missing controller key or controller value, or provided both');
        }
        if (!($this->actionName !== null xor strpos($this->pattern, self::ACTION_KEY) !== false)) {
            throw new InvalidMatcherException('Missing action key or action value, or provided both');
        }
    }

    /**
     * Return an array containing the URI/pattern parts.
     *
     * @param string $uri
     */
    private function prepareForMatching($uri)
    {
        return array_values(array_filter(explode('/', $uri ?? ''), function ($uriPart) {
            return !empty($uriPart);
        }));
    }
}
