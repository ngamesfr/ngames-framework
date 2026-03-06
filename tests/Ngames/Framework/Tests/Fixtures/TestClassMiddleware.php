<?php

namespace Ngames\Framework\Tests\Fixtures;

use Ngames\Framework\Request;
use Ngames\Framework\Response;
use Ngames\Framework\Router\MiddlewareInterface;

class TestClassMiddleware implements MiddlewareInterface
{
    public function handle(Request $request, callable $next): Response
    {
        $response = $next($request);
        $response->setHeader('X-Class-Middleware', 'applied');
        return $response;
    }
}
