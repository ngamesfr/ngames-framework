<?php

declare(strict_types=1);

namespace Ngames\Framework\Tests;

use Ngames\Framework\Input;
use Ngames\Framework\Request;
use PHPUnit\Framework\TestCase;

class InputTest extends TestCase
{
    public function testBody_decodesOnceAndFallsBackToEmptyArray(): void
    {
        $this->assertSame(['a' => 1], $this->input('{"a":1}')->body());
        $this->assertSame([], $this->input('not json')->body());
        $this->assertSame([], $this->input('"scalar"')->body());
        $this->assertSame([], $this->input('')->body());
    }

    public function testString(): void
    {
        $input = $this->input('{"name":"alpha","n":5}');
        $this->assertSame('alpha', $input->string('name'));
        $this->assertSame('', $input->string('n'));
        $this->assertSame('x', $input->string('missing', 'x'));
    }

    public function testInt(): void
    {
        $input = $this->input('{"n":5,"s":"12","f":"1.5","bad":"abc","arr":[1]}');
        $this->assertSame(5, $input->int('n'));
        $this->assertSame(12, $input->int('s'));
        $this->assertSame(1, $input->int('f'));
        $this->assertSame(7, $input->int('bad', 7));
        $this->assertSame(7, $input->int('arr', 7));
        $this->assertSame(0, $input->int('missing'));
    }

    public function testBool(): void
    {
        $input = $this->input('{"t":true,"f":false,"s":"yes","z":"0","arr":[]}');
        $this->assertTrue($input->bool('t'));
        $this->assertFalse($input->bool('f'));
        $this->assertTrue($input->bool('s'));
        $this->assertFalse($input->bool('z'));
        $this->assertTrue($input->bool('arr', true));
        $this->assertFalse($input->bool('missing'));
    }

    public function testQueryAccessors(): void
    {
        $input = new Input(new Request(['page' => '3', 'q' => 'tank', 'zero' => '0', 'empty' => '', 'neg' => '-2', 'list' => ['a']]));
        $this->assertSame(3, $input->queryInt('page'));
        $this->assertSame(9, $input->queryInt('q', 9));
        $this->assertSame(9, $input->queryInt('list', 9));
        $this->assertSame(3, $input->queryPositiveInt('page'));
        $this->assertNull($input->queryPositiveInt('neg'));
        $this->assertNull($input->queryPositiveInt('missing'));
        $this->assertSame('tank', $input->queryString('q'));
        $this->assertSame('d', $input->queryString('list', 'd'));
        $this->assertTrue($input->queryBool('q'));
        $this->assertFalse($input->queryBool('zero'));
        $this->assertFalse($input->queryBool('empty'));
        $this->assertTrue($input->hasQuery('empty'));
        $this->assertFalse($input->hasQuery('missing'));
    }

    public function testFile(): void
    {
        $file = ['name' => 'a.png', 'tmp_name' => '/tmp/x', 'error' => 0];
        $input = new Input(new Request([], [], [], [], ['upload' => $file]));
        $this->assertSame($file, $input->file('upload'));
        $this->assertNull($input->file('missing'));
    }

    private function input(string $rawBody): Input
    {
        return new Input(new Request([], [], [], [], [], $rawBody));
    }
}
