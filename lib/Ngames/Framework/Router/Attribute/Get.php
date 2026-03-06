<?php

namespace Ngames\Framework\Router\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
class Get
{
    public function __construct(public string $path = '')
    {
    }
}
