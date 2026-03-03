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

namespace Ngames\Framework\Storage;

/**
 * PhpSession storage.
 * Uses PhpArray, and is initialized from the session. Values that are changed are written to session at the end of the process.
 *
 */
class PhpSession extends PhpArray implements StorageInterface
{
    protected $storage;

    protected static $instance = null;

    /**
     *
     * @return PhpSession
     */
    public static function getInstance()
    {
        if (self::$instance == null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public static function clearInstance()
    {
        if (self::$instance !== null) {
            self::$instance->reset();
        }

        self::$instance = null;
    }

    // Should not be public but PHP does not allow it.
    public function __construct()
    {
        $this->initializePhpSession();
        $this->storage = $_SESSION;
    }

    public function reset()
    {
        session_destroy();
        $_SESSION = $this->storage = [];
        $this->initializePhpSession();
    }

    public function __destruct()
    {
        $_SESSION = $this->storage;
        session_write_close();
    }

    protected function initializePhpSession()
    {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
    }
}
