<?php

namespace Ngames\Framework\Tests\Fixtures;

use Ngames\Framework\Controller;
use Ngames\Framework\Router\Attribute\Delete;
use Ngames\Framework\Router\Attribute\Get;
use Ngames\Framework\Router\Attribute\Middleware;
use Ngames\Framework\Router\Attribute\Post;
use Ngames\Framework\Router\Attribute\Route;

#[Route('/api/v1/alliances')]
#[Middleware(TestClassMiddleware::class)]
class TestAnnotatedController extends Controller
{
    #[Get]
    public function listAction()
    {
        return $this->ok('list');
    }

    #[Get('/:id')]
    public function showAction(int $id)
    {
        return $this->json(['id' => $id]);
    }

    #[Delete('/:id')]
    #[Middleware(TestMethodMiddleware::class)]
    public function deleteAction(int $id)
    {
        return $this->json(['deleted' => $id]);
    }

    #[Post('/:id/members/:userId/accept')]
    public function acceptAction(int $id, int $userId)
    {
        return $this->json(['id' => $id, 'userId' => $userId]);
    }

    #[Get('/:id/details')]
    public function detailsAction(int $id, string $format = 'json')
    {
        return $this->json(['id' => $id, 'format' => $format]);
    }

    #[Get('/:id/score')]
    public function scoreAction(float $id)
    {
        return $this->json(['id' => $id]);
    }

    #[Get('/:id/active')]
    public function activeAction(bool $id)
    {
        return $this->json(['id' => $id]);
    }
}
