<?php

namespace Ngames\Framework\Tests\Fixtures;

use Ngames\Framework\Controller;

class TestNoAttributeController extends Controller
{
    public function indexAction()
    {
        return $this->ok('index');
    }
}
