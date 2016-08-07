<?php
/*
 * Copyright (c) 2014-2016 Nicolas Braquart
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

use Ngames\Framework\Router\Router;
use Ngames\Framework\Storage\IniFile;

/**
 * Main entrypoint of the framework.
 * The application has to be initialized then run for the request to be executed.
 *
 * @author Nicolas Braquart <nicolas.braquart+ngames@gmail.com>
 */
class Application
{

    /**
     * @var Application
     */
    protected static $instance = null;

    /**
     * @var Autoloader
     */
    protected $autoloader = null;

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
        if (self::$instance != null) {
            require_once __DIR__ . '/Exception.php';
            throw new Exception('The application has already been initialized');
        }
        
        return self::$instance = new self($configurationFile);
    }

    /**
     * Return the single instance of the application
     *
     * @throws Exception
     */
    public static function getInstance()
    {
        // Ensure instance exists
        if (self::$instance == null) {
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
    private function __construct($configurationFile)
    {
        // Register autoload
        require_once __DIR__ . '/Autoloader.php';
        $this->autoloader = new Autoloader();
        $this->autoloader->register();
        
        // Initialize the router
        $this->router = new \Ngames\Framework\Router\Router();
        
        // Initialize the timer
        $this->timer = new \Ngames\Framework\Timer();
        
        // Parse the configuration
        $this->configuration = new IniFile($configurationFile);
        
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
     * @return \Ngames\Framework\Autoloader
     */
    public function getAutoloader()
    {
        return $this->autoloader;
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
     * Whether application is in debug mode or not.
     *
     * @return boolean
     */
    public function isDebug()
    {
        return $this->configuration->debug === '1' || $this->configuration->debug === 'true';
    }

    /**
     * Execute the request
     *
     * @throws Exception
     */
    public function run()
    {
        try {
            // Execute the module/controller/action
            $request = \Ngames\Framework\Request::createRequestFromGlobals();
            $route = $this->router->getRoute($request->getRequestUri());
            $response = null;
            
            if ($route == null) {
                $response = Response::createNotFoundResponse($this->isDebug() ? 'No route matched the requested URI' : null);
            } else {
                $actionResult = Controller::execute($route, $request);
                
                // If not a response object (string typically), constructs it (but it's a default instance)
                if ($actionResult instanceof Response) {
                    $response = $actionResult;
                } elseif (is_string($actionResult)) {
                    $response = new Response();
                    $response->setHeader('Content-Type', 'text/html; charset=utf-8');
                    $response->setContent($actionResult);
                }
            }
            
            if ($response == null) {
                throw new Exception('Invalid response');
            }
            
            // Send the response
            $response->send();
        } catch (\Exception $e) {
            $content = "Internal server error.\n\n" . \Ngames\Framework\Exception::trace($e);
            \Ngames\Framework\Logger::logError($content);
            
            Response::createInternalErrorResponse($this->isDebug() ? $content : null)->send();
        }
    }
}
