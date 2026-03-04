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
 * It is a simple wrapper over the found module/controller/action.
 *
 */
class Route
{
    /**
     *
     * @var string
     */
    protected $moduleName;

    /**
     *
     * @var string
     */
    protected $controllerName;

    /**
     *
     * @var string
     */
    protected $actionName;

    /**
     * @var array
     */
    private $parameters = [];

    /**
     *
     * @param string $moduleName
     * @param string $controllerName
     * @param string $actionName
     * @param array $parameters
     */
    public function __construct($moduleName, $controllerName, $actionName, array $parameters = [])
    {
        $this->moduleName = $moduleName;
        $this->controllerName = $controllerName;
        $this->actionName = $actionName;
        $this->parameters = $parameters;
    }

    /**
     *
     * @return string
     */
    public function getModuleName()
    {
        return $this->moduleName;
    }

    /**
     *
     * @return string
     */
    public function getControllerName()
    {
        return $this->controllerName;
    }

    /**
     *
     * @return string
     */
    public function getActionName()
    {
        return $this->actionName;
    }

    /**
     * @return array
     */
    public function getParameters()
    {
        return $this->parameters;
    }

    /**
     * @param string $name
     * @param mixed $default
     * @return mixed
     */
    public function getParameter($name, $default = null)
    {
        return array_key_exists($name, $this->parameters) ? $this->parameters[$name] : $default;
    }
}
