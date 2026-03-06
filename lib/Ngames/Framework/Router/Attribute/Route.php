<?php

namespace Ngames\Framework\Router\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class Route
{
    public function __construct(public string $path = '')
    {
    }
}
