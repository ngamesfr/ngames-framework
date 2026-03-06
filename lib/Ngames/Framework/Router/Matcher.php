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
 * Matcher class used to identify matching route for a given requested URI.
 */
class Matcher
{
    public const MODULE_KEY = ':module';

    public const CONTROLLER_KEY = ':controller';

    public const ACTION_KEY = ':action';

    private string $pattern;

    private ?string $name;

    private ?string $method;

    private ?string $controllerClass;

    private ?string $actionMethod;

    private array $middlewares;

    // Legacy convention-based fields
    private ?string $moduleName = null;

    private ?string $controllerName = null;

    private ?string $actionName = null;

    /**
     * Create a matcher for an attribute-based route.
     *
     * @param string $pattern The URI pattern (e.g. /api/users/:id)
     * @param string $httpMethod The HTTP method (GET, POST, etc.)
     * @param string $controllerClass Fully qualified controller class name
     * @param string $actionMethod Method name on the controller
     * @param array $middlewares Middleware class names
     * @param string|null $name Optional route name for URL generation
     */
    public function __construct(
        string $pattern,
        string $httpMethod,
        string $controllerClass,
        string $actionMethod,
        array $middlewares = [],
        ?string $name = null
    ) {
        $this->pattern = $pattern;
        $this->method = strtoupper($httpMethod);
        $this->controllerClass = $controllerClass;
        $this->actionMethod = $actionMethod;
        $this->middlewares = $middlewares;
        $this->name = $name;
    }

    /**
     * Create a matcher for convention-based routing (module/controller/action).
     *
     * @deprecated Use attribute-based routing instead. Convention-based routing will be removed in a future version.
     */
    public static function forConventionRoute(
        string $pattern,
        ?string $moduleName = null,
        ?string $controllerName = null,
        ?string $actionName = null,
        ?string $name = null,
        ?string $method = null
    ): self {
        trigger_error(
            'Convention-based routing (module/controller/action) is deprecated. Use attribute-based routing instead.',
            E_USER_DEPRECATED
        );

        $matcher = new self($pattern, $method ?? 'ANY', '', '', [], $name);
        $matcher->initLegacy($moduleName, $controllerName, $actionName, $method);

        return $matcher;
    }

    /**
     * Initialize legacy convention-based fields and validate.
     */
    private function initLegacy(
        ?string $moduleName,
        ?string $controllerName,
        ?string $actionName,
        ?string $method
    ): void {
        $this->moduleName = $moduleName;
        $this->controllerName = $controllerName;
        $this->actionName = $actionName;
        $this->method = $method !== null ? strtoupper($method) : null;
        $this->controllerClass = null;
        $this->actionMethod = null;
        $this->middlewares = [];
        $this->checkConventionRoute();
    }

    /**
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getPattern(): string
    {
        return $this->pattern;
    }

    /**
     * @return string|null
     */
    public function getMethod(): ?string
    {
        return $this->method;
    }

    /**
     * Tries to match the input URI.
     *
     * @param string $uri
     * @param string|null $method
     * @return Route|null
     */
    public function match($uri, $method = null): ?Route
    {
        if ($this->method !== null && $method !== null && strtoupper($method) !== $this->method) {
            return null;
        }

        $result = $this->matchPattern($uri);

        if ($result === null) {
            return null;
        }

        return $this->controllerClass !== null
            ? Route::create($this->controllerClass, $this->actionMethod, $result['parameters'], $this->middlewares)
            : Route::createLegacy($result['moduleName'], $result['controllerName'], $result['actionName'], $result['parameters']);
    }

    /**
     * Match the URI against the pattern and extract route components.
     */
    private function matchPattern($uri): ?array
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
     * Validate convention-based matcher configuration.
     *
     * @throws InvalidMatcherException
     */
    private function checkConventionRoute(): void
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
     */
    private function prepareForMatching($uri): array
    {
        return array_values(array_filter(explode('/', $uri ?? ''), function ($uriPart) {
            return $uriPart !== '';
        }));
    }
}
