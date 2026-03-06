<?php

namespace Ngames\Framework\Router\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
class Middleware
{
    /** @var string[] */
    public array $classes;

    public function __construct(string ...$classes)
    {
        $this->classes = $classes;
    }
}
