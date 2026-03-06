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
     * @param string|null $controllerClass
     * @param string|null $actionMethod
     * @param array $middlewares
     */
    public function __construct(
        $pattern,
        $moduleName = null,
        $controllerName = null,
        $actionName = null,
        $name = null,
        $method = null,
        $controllerClass = null,
        $actionMethod = null,
        array $middlewares = []
    ) {
        $this->pattern = $pattern;
        $this->moduleName = $moduleName;
        $this->controllerName = $controllerName;
        $this->actionName = $actionName;
        $this->name = $name;
        $this->method = $method !== null ? strtoupper($method) : null;
        $this->controllerClass = $controllerClass;
        $this->actionMethod = $actionMethod;
        $this->middlewares = $middlewares;

        // Skip validation for annotated routes (they don't use module/controller/action)
        if ($controllerClass === null) {
            $this->check();
        }
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

        $preparedPattern = $this->prepareForMatching($this->pattern);
        $uri = $this->prepareForMatching($uri);
        $currentModuleName = $this->moduleName;
        $currentControllerName = $this->controllerName;
        $currentActionName = $this->actionName;
        $parameters = [];
        $countPattern = count($preparedPattern);
        $match = true;

        if ($countPattern !== count($uri)) {
            $match = false;
        } else {
            for ($i = 0; $i < $countPattern; $i++) {
                $currentPatternPart = $preparedPattern[$i];
                $currentUriPart = $uri[$i];

                if ($currentPatternPart !== $currentUriPart) {
                    if ($currentPatternPart === self::MODULE_KEY) {
                        $currentModuleName = $currentUriPart;
                    } elseif ($currentPatternPart === self::CONTROLLER_KEY) {
                        $currentControllerName = $currentUriPart;
                    } elseif ($currentPatternPart === self::ACTION_KEY) {
                        $currentActionName = $currentUriPart;
                    } elseif (str_starts_with($currentPatternPart, ':')) {
                        $parameters[substr($currentPatternPart, 1)] = $currentUriPart;
                    } else {
                        $match = false;
                        break;
                    }
                }
            }
        }

        if (!$match) {
            return null;
        }

        // For annotated routes, create a Route with controller class metadata
        if ($this->controllerClass !== null) {
            return new Route(
                $currentModuleName,
                $currentControllerName,
                $currentActionName,
                $parameters,
                $this->controllerClass,
                $this->actionMethod,
                $this->middlewares
            );
        }

        return new Route($currentModuleName, $currentControllerName, $currentActionName, $parameters);
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
