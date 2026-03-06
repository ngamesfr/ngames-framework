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

use Ngames\Framework\Router\Route;
use Ngames\Framework\Utility\Inflector;

/**
 * Controller.
 *
 * This class defines the logic executed when a controller is instanciated.
 * It is the class the application controllers must inherit from.
 *
 */
class Controller
{
    public const CONTROLLER_NAMESPACE = 'Controller';

    public const CONTROLLER_SUFFIX = 'Controller';

    public const ACTION_SUFFIX = 'Action';

    /**
     *
     * @var View
     */
    protected $view;

    /**
     *
     * @var Route
     */
    protected $route;

    /**
     *
     * @var Request
     */
    protected $request;

    /**
     * Default constructor.
     * A view is created with default layout.
     */
    public function __construct()
    {
        $this->view = new View();
        $this->view->setLayout(View::DEFAULT_LAYOUT);
    }

    /**
     * Pre-execute.
     * Does nothing by default, but can be overriden by application.
     */
    protected function preExecute()
    {
    }

    /**
     * Sets the controller request.
     *
     * @param Request $request
     */
    public function setRequest(Request $request)
    {
        $this->request = $request;
    }

    /**
     * Sets the route identified during this request.
     * Default view script is set at this stage.
     *
     * @param Route $route
     */
    public function setRoute(Route $route)
    {
        $this->route = $route;
        $this->view->setScriptFromRoute($this->route);
    }

    // Status helper methods

    /**
     * Return a successful response
     *
     * @param string|null $content
     * @return Response
     */
    protected function ok($content = null)
    {
        return Response::createOkResponse($content);
    }

    /**
     * Return a redirect response
     *
     * @param string $url
     * @return Response
     */
    protected function redirect($url)
    {
        return Response::createRedirectResponse($url);
    }

    /**
     * Return a not found response
     *
     * @param string|null $message
     * @return Response
     */
    protected function notFound($message = null)
    {
        return Response::createNotFoundResponse($message);
    }

    /**
     * Return a bad request response
     *
     * @param string|null $message
     * @return Response
     */
    protected function badRequest($message = null)
    {
        return Response::createBadRequestResponse($message);
    }

    /**
     * Return an internal error response
     *
     * @param string|null $message
     * @return Response
     */
    protected function internalError($message = null)
    {
        return Response::createInternalErrorResponse($message);
    }

    /**
     * Return an unauthorized response
     *
     * @param string|null $message
     * @return Response
     */
    protected function unauthorized($message = null)
    {
        return Response::createUnauthorizedResponse($message);
    }

    /**
     * Forward the request to another action.
     * Contrary to redirect, no HTTP response is sent to the user between the two actions.
     *
     * @param string $actionName
     * @param string|null $controllerName
     * @param string|null $moduleName
     * @return mixed
     */
    protected function forward($actionName, $controllerName = null, $moduleName = null)
    {
        // If module or controller not provided, use current route to determine current ones and use them
        if ($moduleName === null || $controllerName === null) {
            if ($moduleName === null) {
                $moduleName = $this->route->getModuleName();
            }
            if ($controllerName === null) {
                $controllerName = $this->route->getControllerName();
            }
        }

        // Build a new request
        $requestClone = clone $this->request;
        $requestClone->setRequestUri('/' . $moduleName . '/' . $controllerName . '/' . $actionName);

        // Build a new route
        $forwardRoute = new Route($moduleName, $controllerName, $actionName);

        // Execute again for the forward
        return self::execute($forwardRoute, $requestClone);
    }

    /**
     * Return response as JSON.
     *
     * @param mixed $json
     * @param int $options
     *
     * @return \Ngames\Framework\Response
     */
    protected function json($json, $options = JSON_PRETTY_PRINT)
    {
        $response = new Response();
        $response->setContentType('application/json', 'utf-8');
        $response->setContent(json_encode($json, $options));

        return $response;
    }

    /**
     * Execute the provided request.
     *
     * @param Request $request
     * @return mixed
     */
    public static function execute(Route $route, Request $request)
    {
        // Annotated route dispatch path
        if ($route->isAnnotated()) {
            return self::executeAnnotated($route, $request);
        }

        // Get module, controller and action from the route
        $moduleName = $route->getModuleName();
        $controllerName = $route->getControllerName();
        $actionName = $route->getActionName();

        // Build controller class name
        $controllerClassName = self::CONTROLLER_NAMESPACE . '\\';
        $controllerClassName .= ucfirst(Inflector::camelize(str_replace('-', '_', $moduleName))) . '\\';
        $controllerClassName .= ucfirst(Inflector::camelize(str_replace('-', '_', $controllerName)));
        $controllerClassName .= self::CONTROLLER_SUFFIX;

        // Build action method name
        $actionMethodName = Inflector::camelize(str_replace('-', '_', $actionName)) . self::ACTION_SUFFIX;

        // Handle not found (test if class is loadable, exists and method exists)
        if (!class_exists($controllerClassName) || !method_exists($controllerClassName, $actionMethodName)) {
            $message = 'Not found: ' . $controllerClassName . '::' . $actionMethodName . '()';
            \Ngames\Framework\Logger::logWarning($message);

            return Response::createNotFoundResponse(\Ngames\Framework\Application::getInstance()->isDebug() ? $message : null);
        }

        // Create the controller
        $controllerInstance = new $controllerClassName();
        $controllerInstance->setRequest($request);
        $controllerInstance->setRoute($route);

        // Execute pre-execute
        $result = $controllerInstance->preExecute();

        // If pre-execute did not return an output, execute the action
        if ($result === null) {
            $result = $controllerInstance->$actionMethodName();
        }

        return $result;
    }

    /**
     * Execute an annotated route with parameter injection and middleware support.
     *
     * @param Route $route
     * @param Request $request
     * @return mixed
     */
    private static function executeAnnotated(Route $route, Request $request)
    {
        $controllerClassName = $route->getControllerClass();
        $actionMethodName = $route->getActionMethod();

        // Handle not found
        if (!class_exists($controllerClassName) || !method_exists($controllerClassName, $actionMethodName)) {
            $message = 'Not found: ' . $controllerClassName . '::' . $actionMethodName . '()';
            \Ngames\Framework\Logger::logWarning($message);

            return Response::createNotFoundResponse(\Ngames\Framework\Application::getInstance()->isDebug() ? $message : null);
        }

        // Resolve parameters via reflection
        $args = self::resolveParameters($controllerClassName, $actionMethodName, $route);
        if ($args instanceof Response) {
            return $args;
        }

        // Create the controller
        $controllerInstance = new $controllerClassName();
        $controllerInstance->setRequest($request);
        $controllerInstance->setRoute($route);

        // Build the innermost callable (preExecute + action)
        $innerAction = function (Request $request) use ($controllerInstance, $actionMethodName, $args) {
            $result = $controllerInstance->preExecute();
            if ($result === null) {
                $result = $controllerInstance->$actionMethodName(...$args);
            }

            // Ensure we return a Response
            if ($result instanceof Response) {
                return $result;
            } elseif (is_string($result)) {
                $response = new Response();
                $response->setHeader('Content-Type', 'text/html; charset=utf-8');
                $response->setContent($result);
                return $response;
            }

            return new Response();
        };

        // Build middleware chain (wrapping from inside out)
        $next = $innerAction;
        foreach (array_reverse($route->getMiddlewares()) as $middlewareClass) {
            $currentNext = $next;
            $next = function (Request $request) use ($middlewareClass, $currentNext) {
                $middleware = new $middlewareClass();
                return $middleware->handle($request, $currentNext);
            };
        }

        return $next($request);
    }

    /**
     * Resolve action method parameters from route parameters.
     *
     * @param string $controllerClassName
     * @param string $actionMethodName
     * @param Route $route
     * @return array|Response
     */
    private static function resolveParameters($controllerClassName, $actionMethodName, Route $route)
    {
        $reflectionMethod = new \ReflectionMethod($controllerClassName, $actionMethodName);
        $parameters = $reflectionMethod->getParameters();
        $routeParams = $route->getParameters();
        $args = [];

        foreach ($parameters as $param) {
            $name = $param->getName();
            if (array_key_exists($name, $routeParams)) {
                $value = $routeParams[$name];
                $type = $param->getType();
                if ($type instanceof \ReflectionNamedType) {
                    $value = self::castValue($value, $type->getName());
                }
                $args[] = $value;
            } elseif ($param->isDefaultValueAvailable()) {
                $args[] = $param->getDefaultValue();
            } else {
                return Response::createBadRequestResponse('Missing required parameter: ' . $name);
            }
        }

        return $args;
    }

    /**
     * Cast a string value to the given type.
     *
     * @param string $value
     * @param string $type
     * @return mixed
     */
    private static function castValue($value, $type)
    {
        return match ($type) {
            'int' => (int) $value,
            'float' => (float) $value,
            'bool' => filter_var($value, FILTER_VALIDATE_BOOLEAN),
            'string' => (string) $value,
            default => $value,
        };
    }
}
