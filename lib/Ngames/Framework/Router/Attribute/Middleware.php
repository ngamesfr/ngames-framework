<?php

namespace Ngames\Framework\Router\Attribute;

use Attribute;
use Ngames\Framework\Router\MiddlewareInterface;

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
class Middleware
{
    /** @var MiddlewareInterface[] */
    public array $instances;

    public function __construct(MiddlewareInterface ...$instances)
    {
        $this->instances = $instances;
    }
}
