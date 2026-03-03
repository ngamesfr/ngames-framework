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

use Ngames\Framework\Tests\Database\Model\Book;
use Ngames\Framework\Tests\Database\Model\Author;

class FinderTest extends AbstractDatabaseTestCase
{
    public function testQuery()
    {
        $result = Book::getFinder()->query(
            'SELECT b.*, a.last_name as author_last_name, a.first_name as author_first_name ' .
            'FROM book b JOIN author a ON b.author_id = a.id'
        );

        $this->assertEquals(3, count($result));
        $this->assertInstanceOf(Book::class, $result[0]);
        $this->assertInstanceOf(Book::class, $result[1]);
        $this->assertInstanceOf(Book::class, $result[2]);
        $this->assertEquals(1, $result[0]->id);
        $this->assertEquals('Book 1', $result[0]->title);
        $this->assertEquals('Description 1', $result[0]->description);
        $this->assertInstanceOf(Author::class, $result[0]->author);
        $this->assertEquals(1, $result[0]->author->id);
        $this->assertEquals('Last Name 1', $result[0]->author->lastName);
        $this->assertEquals('Setter: First Name 1', $result[0]->author->firstName);
    }

    public function testQueryOne()
    {
        $result = Book::getFinder()->queryOne(
            'SELECT b.*, a.last_name as author_last_name, a.first_name as author_first_name ' .
            'FROM book b JOIN author a ON b.author_id = a.id ' .
            'WHERE b.id = ?',
            array(3)
        );

        $this->assertInstanceOf(Book::class, $result);
        $this->assertEquals(3, $result->id);
        $this->assertEquals('Book 3', $result->title);
        $this->assertEquals('Description 3', $result->description);
        $this->assertInstanceOf(Author::class, $result->author);
        $this->assertEquals(2, $result->author->id);
        $this->assertEquals('Last Name 2', $result->author->lastName);
        $this->assertEquals('Setter: First Name 2', $result->author->firstName);
    }
}
