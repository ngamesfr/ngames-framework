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
 * A route returned by the router.
 */
class Route
{
    private ?string $controllerClass;

    private ?string $actionMethod;

    private array $parameters;

    private array $middlewares;

    // Legacy convention-based fields
    private ?string $moduleName;

    private ?string $controllerName;

    private ?string $actionName;

    private function __construct()
    {
        // Private: use Route::create() or Route::createLegacy() named constructors
    }

    /**
     * Create an annotated route.
     */
    public static function create(
        string $controllerClass,
        string $actionMethod,
        array $parameters = [],
        array $middlewares = []
    ): self {
        $route = new self();
        $route->controllerClass = $controllerClass;
        $route->actionMethod = $actionMethod;
        $route->parameters = $parameters;
        $route->middlewares = $middlewares;
        $route->moduleName = null;
        $route->controllerName = null;
        $route->actionName = null;
        return $route;
    }

    /**
     * Create a convention-based route (legacy).
     *
     * @deprecated Use attribute-based routing instead.
     */
    public static function createLegacy(
        string $moduleName,
        string $controllerName,
        string $actionName,
        array $parameters = []
    ): self {
        $route = new self();
        $route->moduleName = $moduleName;
        $route->controllerName = $controllerName;
        $route->actionName = $actionName;
        $route->parameters = $parameters;
        $route->controllerClass = null;
        $route->actionMethod = null;
        $route->middlewares = [];
        return $route;
    }

    public function isAnnotated(): bool
    {
        return $this->controllerClass !== null;
    }

    public function getControllerClass(): ?string
    {
        return $this->controllerClass;
    }

    public function getActionMethod(): ?string
    {
        return $this->actionMethod;
    }

    public function getParameters(): array
    {
        return $this->parameters;
    }

    /**
     * @param mixed $default
     * @return mixed
     */
    public function getParameter(string $name, $default = null)
    {
        return $this->parameters[$name] ?? $default;
    }

    public function getMiddlewares(): array
    {
        return $this->middlewares;
    }

    /**
     * @deprecated Legacy convention-based routing field.
     */
    public function getModuleName(): ?string
    {
        return $this->moduleName;
    }

    /**
     * @deprecated Legacy convention-based routing field.
     */
    public function getControllerName(): ?string
    {
        return $this->controllerName;
    }

    /**
     * @deprecated Legacy convention-based routing field.
     */
    public function getActionName(): ?string
    {
        return $this->actionName;
    }
}
