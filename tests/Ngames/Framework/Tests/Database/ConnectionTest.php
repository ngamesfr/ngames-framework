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

class ConnectionTest extends AbstractDatabaseTestCase
{
    public function testQuery()
    {
        $result = Connection::query('SELECT * FROM book ORDER BY id');

        $this->assertEquals(3, count($result));

        $this->assertEquals(1, $result[0]['id']);
        $this->assertEquals('Book 1', $result[0]['title']);
        $this->assertEquals('Description 1', $result[0]['description']);
        $this->assertEquals(1, $result[0]['author_id']);

        $this->assertEquals(2, $result[1]['id']);
        $this->assertEquals('Book 2', $result[1]['title']);
        $this->assertEquals('Description 2', $result[1]['description']);
        $this->assertEquals(1, $result[1]['author_id']);

        $this->assertEquals(3, $result[2]['id']);
        $this->assertEquals('Book 3', $result[2]['title']);
        $this->assertEquals('Description 3', $result[2]['description']);
        $this->assertEquals(2, $result[2]['author_id']);
    }

    public function testQuery_error()
    {
        $this->expectException('\Ngames\Framework\Exception');
        $this->expectExceptionMessage('Caught PDO exception');
        Connection::query('SELECT * FROM does_not_exist');
    }

    public function testExec_delete()
    {
        $this->assertEquals(3, count(Connection::query('SELECT * FROM book ORDER BY id')));
        $this->assertEquals(2, Connection::exec('DELETE FROM book WHERE id=1 OR id=2'));
        $this->assertEquals(1, count(Connection::query('SELECT * FROM book ORDER BY id')));
    }

    public function testExec_update()
    {
        $this->assertEquals(array(
            array(
                'id' => 1,
                'title' => 'Book 1',
                'description' => 'Description 1',
                'author_id' => 1
            )
        ), Connection::query('SELECT * FROM book WHERE id=?', array(1)));
        $this->assertEquals(1, Connection::exec('UPDATE book SET title=:title WHERE id=:id', array('title' => 'New Book 1', 'id' => 1)));
        $this->assertEquals(array(
            array(
                'id' => 1,
                'title' => 'New Book 1',
                'description' => 'Description 1',
                'author_id' => 1
            )
        ), Connection::query('SELECT * FROM book WHERE id=?', array(1)));
    }

    public function testExec_error()
    {
        $this->expectException('\Ngames\Framework\Exception');
        $this->expectExceptionMessage('Caught PDO exception');
        Connection::exec('DELETE FROM does_not_exist');
    }

    public function testCount()
    {
        // SQLite does not support rowCount properly
        $this->assertEquals(0, Connection::count('SELECT * FROM book'));
    }

    public function testCount_error()
    {
        $this->expectException('\Ngames\Framework\Exception');
        $this->expectExceptionMessage('Caught PDO exception');
        Connection::count('SELECT * FROM does_not_exist');
    }

    public function testQueryOne()
    {
        $this->assertEquals(array(
            'id' => 3,
            'title' => 'Book 3',
            'description' => 'Description 3',
            'author_id' => 2
        ), Connection::queryOne('SELECT * FROM book ORDER BY id DESC'));
    }

    public function testQueryOne_error()
    {
        $this->expectException('\Ngames\Framework\Exception');
        $this->expectExceptionMessage('Caught PDO exception');
        Connection::queryOne('SELECT * FROM does_not_exist');
    }

    public function testInsert()
    {
        $this->assertEquals(3, count(Connection::query('SELECT * FROM book')));
        $this->assertEquals(4, Connection::insert('book', array(
            'id' => 4,
            'title' => 'Book 4',
            'description' => 'Description 4',
            'author_id' => 1
        )));
        $this->assertEquals(4, count(Connection::query('SELECT * FROM book')));
        $this->assertEquals(array(
            'id' => 4,
            'title' => 'Book 4',
            'description' => 'Description 4',
            'author_id' => 1
        ), Connection::queryOne('SELECT * FROM book WHERE id=?', array(4)));
    }

    public function testInsert_error()
    {
        $this->expectException('\Ngames\Framework\Exception');
        $this->expectExceptionMessage('Caught PDO exception');
        $this->assertEquals(4, Connection::insert('does_not_exist', array('id' => 1)));
    }

    public function testFindOneById()
    {
        $this->assertEquals(array(
            'id' => 1,
            'title' => 'Book 1',
            'description' => 'Description 1',
            'author_id' => 1
        ), Connection::findOneById('book', 1));
    }

    public function testFindOneById_error()
    {
        $this->expectException('\Ngames\Framework\Exception');
        $this->expectExceptionMessage('Caught PDO exception');
        Connection::findOneById('does_not_exist', 1);
    }

    public function testGetLastError()
    {
        $this->assertIsArray(Connection::getLastError());
        $this->assertEquals(3, count(Connection::getLastError()));
    }

    public function testGetQueryCounter()
    {
        $this->assertIsNumeric(Connection::getQueryCounter());
    }

    public function testGetQueries()
    {
        $this->assertIsArray(Connection::getQueries());
    }
}
