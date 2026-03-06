<?php

namespace Ngames\Framework\Tests\Fixtures;

use Ngames\Framework\Request;
use Ngames\Framework\Response;
use Ngames\Framework\Router\MiddlewareInterface;

class TestShortCircuitMiddleware implements MiddlewareInterface
{
    public function handle(Request $request, callable $next): Response
    {
        return Response::createUnauthorizedResponse('Blocked by middleware');
    }
}
