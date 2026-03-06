<?php

namespace Ngames\Framework\Router;

use Ngames\Framework\Router\Attribute\Delete;
use Ngames\Framework\Router\Attribute\Get;
use Ngames\Framework\Router\Attribute\Middleware;
use Ngames\Framework\Router\Attribute\Patch;
use Ngames\Framework\Router\Attribute\Post;
use Ngames\Framework\Router\Attribute\Put;
use Ngames\Framework\Router\Attribute\Route as RouteAttribute;
use Ngames\Framework\Router\MiddlewareInterface;

class RouteCollector
{
    private const HTTP_METHOD_ATTRIBUTES = [
        Get::class => 'GET',
        Post::class => 'POST',
        Put::class => 'PUT',
        Patch::class => 'PATCH',
        Delete::class => 'DELETE',
    ];

    private string $cachePrefix;

    private bool $apcuWarningLogged = false;

    public function __construct(string $cachePrefix = 'ngames_routes')
    {
        $this->cachePrefix = $cachePrefix;
    }

    /**
     * Collect annotated routes from the given directories and register them on the router.
     *
     * @param array $directories
     * @param Router $router
     */
    public function collect(array $directories, Router $router): void
    {
        $routes = $this->loadRoutes($directories);

        foreach ($routes as $routeData) {
            $router->addMatcher(new Matcher(
                $routeData['pattern'],
                $routeData['method'],
                $routeData['controllerClass'],
                $routeData['actionMethod'],
                $routeData['middlewares']
            ));
        }
    }

    /**
     * Clear the APCu cache.
     */
    public function clearCache(): void
    {
        if (function_exists('apcu_delete')) {
            apcu_delete($this->getCacheKey());
        }
    }

    /**
     * Load routes, using APCu cache if available.
     *
     * @param array $directories
     * @return array
     */
    private function loadRoutes(array $directories): array
    {
        $cacheKey = $this->getCacheKey();

        // Try APCu cache
        if (function_exists('apcu_fetch') && function_exists('apcu_enabled') && apcu_enabled()) {
            $cached = apcu_fetch($cacheKey, $success);
            if ($success) {
                return $cached;
            }

            $routes = $this->scanDirectories($directories);
            apcu_store($cacheKey, $routes);
            return $routes;
        }

        // APCu not available — log warning once
        if (!$this->apcuWarningLogged) {
            $this->apcuWarningLogged = true;
            \Ngames\Framework\Logger::logWarning('APCu is not available, route caching disabled. Routes will be scanned on every request.');
        }

        return $this->scanDirectories($directories);
    }

    /**
     * Scan directories for annotated controller classes.
     *
     * @param array $directories
     * @return array
     */
    private function scanDirectories(array $directories): array
    {
        $routes = [];

        foreach ($directories as $directory) {
            if (!is_dir($directory)) {
                continue;
            }
            $this->scanDirectory($directory, $routes);
        }

        return $routes;
    }

    /**
     * Recursively scan a directory for PHP files.
     *
     * @param string $directory
     * @param array &$routes
     */
    private function scanDirectory(string $directory, array &$routes): void
    {
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($directory, \RecursiveDirectoryIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            if ($file->getExtension() !== 'php') {
                continue;
            }

            $className = $this->getClassNameFromFile($file->getPathname());
            if ($className === null || !class_exists($className)) {
                continue;
            }

            $this->processClass($className, $routes);
        }
    }

    /**
     * Extract the fully qualified class name from a PHP file.
     *
     * @param string $filePath
     * @return string|null
     */
    private function getClassNameFromFile(string $filePath): ?string
    {
        $tokens = token_get_all(file_get_contents($filePath));
        $namespace = null;
        $class = null;

        for ($i = 0, $count = count($tokens); $i < $count; $i++) {
            if (!is_array($tokens[$i])) {
                continue;
            }

            if ($tokens[$i][0] === T_NAMESPACE) {
                $namespace = $this->extractTokenValue($tokens, $i, [T_NAME_QUALIFIED, T_STRING]);
            } elseif ($tokens[$i][0] === T_CLASS && !$this->isPrecededBy($tokens, $i, T_DOUBLE_COLON)) {
                $class = $this->extractTokenValue($tokens, $i, [T_STRING]);
                break;
            }
        }

        if ($class === null) {
            return null;
        }

        return $namespace !== null ? $namespace . '\\' . $class : $class;
    }

    private function extractTokenValue(array $tokens, int $index, array $expectedTokens): ?string
    {
        $count = count($tokens);

        for ($i = $index + 1; $i < $count; $i++) {
            if (!is_array($tokens[$i])) {
                break;
            }

            if (in_array($tokens[$i][0], $expectedTokens)) {
                return $tokens[$i][1];
            }

            if ($tokens[$i][0] !== T_WHITESPACE) {
                break;
            }
        }

        return null;
    }

    private function isPrecededBy(array $tokens, int $index, int $tokenType): bool
    {
        for ($i = $index - 1; $i >= 0; $i--) {
            if (!is_array($tokens[$i]) || $tokens[$i][0] !== T_WHITESPACE) {
                return is_array($tokens[$i]) && $tokens[$i][0] === $tokenType;
            }
        }

        return false;
    }

    /**
     * Process a class for route attributes.
     *
     * @param string $className
     * @param array &$routes
     */
    private function processClass(string $className, array &$routes): void
    {
        $reflectionClass = new \ReflectionClass($className);
        $routeAttributes = $reflectionClass->getAttributes(RouteAttribute::class);

        if (empty($routeAttributes)) {
            return;
        }

        $routeAttribute = $routeAttributes[0]->newInstance();
        $basePath = $routeAttribute->path;

        // Collect class-level middleware
        $classMiddlewares = [];
        foreach ($reflectionClass->getAttributes(Middleware::class) as $attr) {
            $classes = $attr->newInstance()->classes;
            $this->validateMiddlewareClasses($classes, $className);
            array_push($classMiddlewares, ...$classes);
        }

        // Process methods
        foreach ($reflectionClass->getMethods(\ReflectionMethod::IS_PUBLIC) as $method) {
            $this->processMethod($method, $basePath, $className, $classMiddlewares, $routes);
        }
    }

    private function validateMiddlewareClasses(array $classes, string $context): void
    {
        foreach ($classes as $class) {
            if (!is_subclass_of($class, MiddlewareInterface::class)) {
                throw new \InvalidArgumentException(sprintf(
                    'Middleware "%s" declared on %s must implement %s',
                    $class,
                    $context,
                    MiddlewareInterface::class
                ));
            }
        }
    }

    /**
     * Process a single method for HTTP method attributes.
     */
    private function processMethod(\ReflectionMethod $method, string $basePath, string $className, array $classMiddlewares, array &$routes): void
    {
        $methodMiddlewares = [];
        foreach ($method->getAttributes(Middleware::class) as $mwAttr) {
            $classes = $mwAttr->newInstance()->classes;
            $this->validateMiddlewareClasses($classes, $className . '::' . $method->getName());
            array_push($methodMiddlewares, ...$classes);
        }
        $allMiddlewares = array_merge($classMiddlewares, $methodMiddlewares);

        foreach (self::HTTP_METHOD_ATTRIBUTES as $attributeClass => $httpMethod) {
            foreach ($method->getAttributes($attributeClass) as $methodAttribute) {
                $fullPath = rtrim($basePath, '/') . '/' . ltrim($methodAttribute->newInstance()->path, '/');
                $fullPath = rtrim($fullPath, '/') ?: '/';

                $routes[] = [
                    'pattern' => $fullPath,
                    'method' => $httpMethod,
                    'controllerClass' => $className,
                    'actionMethod' => $method->getName(),
                    'middlewares' => $allMiddlewares,
                    'name' => null,
                ];
            }
        }
    }

    /**
     * @return string
     */
    private function getCacheKey(): string
    {
        return $this->cachePrefix . '_compiled';
    }
}
