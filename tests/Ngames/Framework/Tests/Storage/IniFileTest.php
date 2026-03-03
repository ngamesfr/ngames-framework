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

namespace Ngames\Framework\Tests\Storage;

use Ngames\Framework\Storage\IniFile;

class IniFileTest extends \PHPUnit\Framework\TestCase
{
    public function testConstructor_failure()
    {
        $this->expectException('\Ngames\Framework\Exception');
        $iniFile = new IniFile(ROOT_DIR . '/tests/data/input_does_not_exist.ini');
    }

    public function testParseFile()
    {
        define('CONSTANT', 'constant_value');
        $iniFile = new IniFile(ROOT_DIR . '/tests/data/Storage/IniFile/input.ini');

        $this->assertEquals('127.0.0.1', $iniFile['framework']['database']['host']);
        $this->assertEquals('127.0.0.1', $iniFile->framework->database->host);
        $this->assertEquals('db_username', $iniFile->framework->database->username);
        $this->assertEquals('db_password', $iniFile->framework->database->password);
        $this->assertEquals('constant_value/logs/application.log', $iniFile->framework->log->destination);
        $this->assertEquals('debug', $iniFile->framework->log->level);
        $this->assertEquals('1', $iniFile->framework->debug);
        $this->assertEquals('John Smith', $iniFile->users[0]);
        $this->assertEquals('John Smith', $iniFile['users'][0]);
        $this->assertEquals('Jane Doe', $iniFile->users[1]);
    }

    public function testWriteFile()
    {
        $data = array('key1' => 'val1', 'key2' => 'val2');
        ob_start();
        IniFile::writeFile('php://output', $data);
        $output = ob_get_contents();
        ob_end_clean();

        $this->assertEquals('key1=val1' . "\n" . 'key2=val2' . "\n", $output);
    }
}
