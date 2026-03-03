<?php

/*
 * Copyright (c) 2014-2021 NGames
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is furnished
 * to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

namespace Controller\Application;

use Ngames\Framework\Controller;

class DummyController extends Controller
{
    public function indexAction()
    {
        return $this->ok('index');
    }

    public function outputStringAction()
    {
        return 'output_string';
    }

    public function outputNullAction()
    {
        return null;
    }

    public function okAction()
    {
        return $this->ok('ok');
    }

    public function redirectAction()
    {
        return $this->redirect('url');
    }

    public function notFoundAction()
    {
        return $this->notFound('not_found');
    }

    public function badRequestAction()
    {
        return $this->badRequest('bad_request');
    }

    public function internalErrorAction()
    {
        return $this->internalError('internal_error');
    }

    public function unauthorizedAction()
    {
        return $this->unauthorized('unauthorized');
    }

    public function jsonAction()
    {
        return $this->json(array('key' => 'value'));
    }

    public function forwardAction()
    {
        return $this->forward('forwardAfter');
    }

    public function forwardAfterAction()
    {
        return $this->ok('forward_after');
    }
}
