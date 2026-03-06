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

    protected function ok($content = null)
    {
        return Response::createOkResponse($content);
    }

    protected function redirect($url)
    {
        return Response::createRedirectResponse($url);
    }

    protected function notFound($message = null)
    {
        return Response::createNotFoundResponse($message);
    }

    protected function badRequest($message = null)
    {
        return Response::createBadRequestResponse($message);
    }

    protected function internalError($message = null)
    {
        return Response::createInternalErrorResponse($message);
    }

    protected function unauthorized($message = null)
    {
        return Response::createUnauthorizedResponse($message);
    }

    /**
     * Forward the request to another action.
     * Contrary to redirect, no HTTP response is sent to the user between the two actions.
     *
     * @deprecated Use attribute-based routing instead of convention-based forwarding.
     *
     * @param string $actionName
     * @param string|null $controllerName
     * @param string|null $moduleName
     * @return mixed
     */
    protected function forward($actionName, $controllerName = null, $moduleName = null)
    {
        if ($moduleName === null) {
            $moduleName = $this->route->getModuleName();
        }
        if ($controllerName === null) {
            $controllerName = $this->route->getControllerName();
        }

        $requestClone = clone $this->request;
        $requestClone->setRequestUri('/' . $moduleName . '/' . $controllerName . '/' . $actionName);

        $forwardRoute = Route::createLegacy($moduleName, $controllerName, $actionName);

        return self::execute($forwardRoute, $requestClone);
    }

    /**
     * Return response as JSON.
     *
     * @param mixed $json
     * @param int $options
     *
     * @return Response
     */
    protected function json($json, $options = JSON_PRETTY_PRINT)
    {
        $response = new Response();
        $response->setContentType('application/json', 'utf-8');
        $response->setContent(json_encode($json, $options));

        return $response;
    }

    /**
     * Execute a route.
     *
     * @param Route $route
     * @param Request $request
     * @return mixed
     */
    public static function execute(Route $route, Request $request)
    {
        if ($route->isAnnotated()) {
            return self::executeAnnotated($route, $request);
        }

        return self::executeLegacy($route, $request);
    }

    /**
     * Execute an annotated route with parameter injection and middleware support.
     */
    private static function executeAnnotated(Route $route, Request $request)
    {
        $controllerClassName = $route->getControllerClass();
        $actionMethodName = $route->getActionMethod();

        if (!class_exists($controllerClassName) || !method_exists($controllerClassName, $actionMethodName)) {
            return self::notFoundResponse($controllerClassName, $actionMethodName);
        }

        $args = self::resolveParameters($controllerClassName, $actionMethodName, $route);
        if ($args instanceof Response) {
            return $args;
        }

        $controllerInstance = self::createController($controllerClassName, $route, $request);

        $innerAction = function (Request $request) use ($controllerInstance, $actionMethodName, $args) {
            $result = $controllerInstance->preExecute();
            if ($result === null) {
                $result = $controllerInstance->$actionMethodName(...$args);
            }

            return self::toResponse($result);
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
     * Execute a convention-based route (legacy).
     *
     * @deprecated Use attribute-based routing instead.
     */
    private static function executeLegacy(Route $route, Request $request)
    {
        $moduleName = $route->getModuleName();
        $controllerName = $route->getControllerName();
        $actionName = $route->getActionName();

        $controllerClassName = self::CONTROLLER_NAMESPACE . '\\';
        $controllerClassName .= ucfirst(Inflector::camelize(str_replace('-', '_', $moduleName))) . '\\';
        $controllerClassName .= ucfirst(Inflector::camelize(str_replace('-', '_', $controllerName)));
        $controllerClassName .= self::CONTROLLER_SUFFIX;

        $actionMethodName = Inflector::camelize(str_replace('-', '_', $actionName)) . self::ACTION_SUFFIX;

        if (!class_exists($controllerClassName) || !method_exists($controllerClassName, $actionMethodName)) {
            return self::notFoundResponse($controllerClassName, $actionMethodName);
        }

        $controllerInstance = self::createController($controllerClassName, $route, $request);

        $result = $controllerInstance->preExecute();
        if ($result === null) {
            $result = $controllerInstance->$actionMethodName();
        }

        return $result;
    }

    /**
     * Instantiate a controller and bind the route and request to it.
     */
    private static function createController(string $className, Route $route, Request $request): self
    {
        $controller = new $className();
        $controller->setRequest($request);
        $controller->setRoute($route);

        return $controller;
    }

    /**
     * Create a not-found response with optional debug info.
     */
    private static function notFoundResponse(string $className, string $methodName): Response
    {
        $message = 'Not found: ' . $className . '::' . $methodName . '()';
        Logger::logWarning($message);

        return Response::createNotFoundResponse(Application::getInstance()->isDebug() ? $message : null);
    }

    /**
     * Convert an action result to a Response.
     */
    private static function toResponse($result): Response
    {
        if ($result instanceof Response) {
            return $result;
        }

        if (is_string($result)) {
            $response = new Response();
            $response->setHeader('Content-Type', 'text/html; charset=utf-8');
            $response->setContent($result);

            return $response;
        }

        return new Response();
    }

    /**
     * Resolve action method parameters from route parameters.
     *
     * @return array|Response
     */
    private static function resolveParameters(string $className, string $methodName, Route $route)
    {
        $reflectionMethod = new \ReflectionMethod($className, $methodName);
        $routeParams = $route->getParameters();
        $args = [];

        foreach ($reflectionMethod->getParameters() as $param) {
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
     * @return mixed
     */
    private static function castValue(string $value, string $type)
    {
        return match ($type) {
            'int' => (int) $value,
            'float' => (float) $value,
            'bool' => filter_var($value, FILTER_VALIDATE_BOOLEAN),
            default => $value,
        };
    }
}
