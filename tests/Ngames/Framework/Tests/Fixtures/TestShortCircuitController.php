<?php

namespace Ngames\Framework\Tests\Fixtures;

use Ngames\Framework\Controller;
use Ngames\Framework\Router\Attribute\Get;
use Ngames\Framework\Router\Attribute\Middleware;
use Ngames\Framework\Router\Attribute\Route;

#[Route('/api/v1/blocked')]
#[Middleware(TestShortCircuitMiddleware::class)]
class TestShortCircuitController extends Controller
{
    #[Get]
    public function indexAction()
    {
        return $this->ok('should not reach here');
    }
}
