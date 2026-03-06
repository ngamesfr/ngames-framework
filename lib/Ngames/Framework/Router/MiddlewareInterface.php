<?php

namespace Ngames\Framework\Router;

use Ngames\Framework\Request;
use Ngames\Framework\Response;

interface MiddlewareInterface
{
    public function handle(Request $request, callable $next): Response;
}
