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

namespace Ngames\Framework\Tests\Database;

use Ngames\Framework\Database\Connection;

/**
 * Common test case class for all tests involving a database
 */
class AbstractDatabaseTestCase extends \PHPUnit\Framework\TestCase
{
    public static function setUpBeforeClass(): void
    {
        DummyConnection::getConnection()->exec('CREATE TABLE book(id INT, title VARCHAR(100), description VARCHAR(100), author_id INT)');
        DummyConnection::getConnection()->exec('CREATE TABLE author(id INT, last_name VARCHAR(100), first_name VARCHAR(100))');
    }

    public function setUp(): void
    {
        DummyConnection::getConnection()->exec("INSERT INTO author VALUES(1, 'Last Name 1', 'First Name 1')");
        DummyConnection::getConnection()->exec("INSERT INTO author VALUES(2, 'Last Name 2', 'First Name 2')");
        DummyConnection::getConnection()->exec("INSERT INTO book VALUES(1, 'Book 1', 'Description 1', 1)");
        DummyConnection::getConnection()->exec("INSERT INTO book VALUES(2, 'Book 2', 'Description 2', 1)");
        DummyConnection::getConnection()->exec("INSERT INTO book VALUES(3, 'Book 3', 'Description 3', 2)");
    }

    public function tearDown(): void
    {
        DummyConnection::getConnection()->exec('DELETE FROM book');
        DummyConnection::getConnection()->exec('DELETE FROM author');
    }

    public static function tearDownAfterClass(): void
    {
        DummyConnection::getConnection()->exec('DROP TABLE book');
        DummyConnection::getConnection()->exec('DROP TABLE author');
    }
}

/**
 * This class allows to override the PDO object within the Connection database.
 *
 */
class DummyConnection extends Connection
{
    public static function getConnection()
    {
        if (!parent::$connection) {
            parent::$connection = new \PDO('sqlite::memory:', null, null, [
                \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION
            ]);
        }

        return parent::$connection;
    }
}
