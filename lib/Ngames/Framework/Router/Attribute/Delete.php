<?php

namespace Ngames\Framework\Router\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
class Delete
{
    public function __construct(public string $path = '')
    {
    }
}
