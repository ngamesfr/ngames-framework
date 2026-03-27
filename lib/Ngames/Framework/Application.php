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

use Ngames\Framework\Router\RouteCollector;
use Ngames\Framework\Router\Router;
use Ngames\Framework\Storage\IniFile;

/**
 * Main entrypoint of the framework.
 * The application has to be initialized then run for the request to be executed.
 *
 */
class Application
{
    /**
     * @var Application
     */
    protected static $instance = null;

    /**
     * @var Router
     */
    protected $router = null;

    /**
     * @var IniFile
     */
    protected $configuration = null;

    /**
     * @var Timer
     */
    protected $timer = null;

    /**
     *
     * @param string $configurationFile
     *            path to the configuration file (cf README for configuration file infos)
     *
     * @throws Exception
     */
    public static function initialize($configurationFile)
    {
        // First check an instance does not already exists
        if (self::$instance instanceof self) {
            require_once __DIR__ . '/Exception.php';
            throw new Exception('The application has already been initialized');
        }

        // Uses late static binding, in case parent constructor was overriden in possible child class
        return self::$instance = new static($configurationFile);
    }

    /**
     * Return the single instance of the application
     *
     * @throws Exception
     */
    public static function getInstance()
    {
        // Ensure instance exists
        if (!(self::$instance instanceof self)) {
            require_once __DIR__ . '/Exception.php';
            throw new Exception('The application has not been initialized');
        }

        return self::$instance;
    }

    /**
     * Initializes a new application
     *
     * @param string $configurationFile
     */
    protected function __construct($configurationFile)
    {
        // Parse the configuration
        $this->configuration = new IniFile($configurationFile);

        // Initialize the router
        $this->router = new \Ngames\Framework\Router\Router();

        // Initialize the timer
        $this->timer = new \Ngames\Framework\Timer();

        // Intialize the logging facility if needed
        if ($this->configuration->has('log')) {
            $destination = $this->configuration->log->destination;
            $constantName = '\Ngames\Framework\Logger::LEVEL_' . strtoupper($this->configuration->log->level);

            // Initialize the logger if possible
            if (defined($constantName)) {
                $level = constant($constantName);
                \Ngames\Framework\Logger::initialize($destination, $level);
            }
        }
    }

    /**
     *
     * @return IniFile
     */
    public function getConfiguration()
    {
        return $this->configuration;
    }

    /**
     *
     * @return \Ngames\Framework\Timer
     */
    public function getTimer()
    {
        return $this->timer;
    }

    /**
     * Return the Router instance of the application.
     *
     * @return \Ngames\Framework\Router\Router
     */
    public function getRouter()
    {
        return $this->router;
    }

    /**
     * Register annotated controllers from the given directories.
     *
     * @param array|null $directories
     */
    public function registerAnnotatedControllers(?array $directories = null): void
    {
        if ($directories === null) {
            $directories = [ROOT_DIR . '/src/Controller'];
        }

        $collector = new RouteCollector();
        $collector->collect($directories, $this->router);
    }

    /**
     * Whether application is in debug mode or not.
     *
     * @return boolean
     */
    public function isDebug()
    {
        return $this->configuration->debug === '1' || $this->configuration->debug === 'true';
    }

    /**
     * Process a request and return the response without sending it.
     * This is the testable core of the request lifecycle.
     */
    public function handle(Request $request): Response
    {
        $this->registerAnnotatedControllers();

        $route = $this->router->getRoute($request->getRequestUri(), $request->getMethod());

        if ($route === null) {
            return Response::createNotFoundResponse($this->isDebug() ? 'No route matched the requested URI' : null);
        }

        $request->mergeGetParameters($route->getParameters());
        $response = Controller::execute($route, $request);

        if ($response === null) {
            throw new Exception('Invalid response');
        }

        // Legacy convention-based routes may return strings
        if (is_string($response)) {
            $stringResult = $response;
            $response = new Response();
            $response->setHeader('Content-Type', 'text/html; charset=utf-8');
            $response->setContent($stringResult);
        }

        return $response;
    }

    /**
     * Execute the request (production entry point).
     * Builds a Request from superglobals, delegates to handle(), sends the response.
     */
    public function run()
    {
        try {
            $request = new Request($_GET, $_POST, $_COOKIE, $_SERVER, $_FILES, file_get_contents('php://input'));
            $this->handle($request)->send();
        } catch (\Throwable $e) {
            $content = "Internal server error.\n\n" . Exception::trace($e);
            Logger::logError($content);

            Response::createInternalErrorResponse($this->isDebug() ? $content : null)->send();
        }
    }
}
