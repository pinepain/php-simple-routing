<?php


namespace Pinepain\SimpleRouting\Tests;

use Pinepain\SimpleRouting\Compiler;

class CompilerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Compiler
     */
    private $compiler;

    protected function setUp()
    {
        parent::setUp();

        $this->compiler = new Compiler();
    }

    /**
     * @covers \Pinepain\SimpleRouting\Compiler::validateFormat
     */
    public function testValidateFormatSuccess()
    {
        $compiler = $this->compiler;

        $compiler->validateFormat('name', 'format');
        $compiler->validateFormat('name', 'test');
    }

    /**
     * @covers                   \Pinepain\SimpleRouting\Compiler::validateFormat
     *
     * @expectedException \Pinepain\SimpleRouting\Exception
     * @expectedExceptionMessage Invalid format regex
     *
     * @dataProvider             provideValidateFormatFailureFormat
     */
    public function testValidateFormatFailureFormat($regex)
    {
        $compiler = $this->compiler;

        $compiler->validateFormat('name', $regex);
    }

    /**
     * @covers                   \Pinepain\SimpleRouting\Compiler::validateFormat
     *
     * @expectedException \Pinepain\SimpleRouting\Exception
     * @expectedExceptionMessage Catching groups in regex
     *
     * @dataProvider             provideValidateFormatFailureCapturing
     */
    public function testValidateFormatFailureCapturing($regex)
    {
        $compiler = $this->compiler;

        $compiler->validateFormat('name', $regex);
    }

    /**
     * @covers \Pinepain\SimpleRouting\Compiler::compile
     *
     */
    public function testCompile()
    {
        $compiler = $this->compiler;

        $res = $compiler->compile(['static']);
        $this->assertInstanceOf('\Pinepain\SimpleRouting\CompiledRoute', $res);
        $this->assertEquals('static', $res->getRegex());
        $this->assertSame([], $res->getVariables());

        $res = $compiler->compile(['static', ['param', false, false, false]]);
        $this->assertInstanceOf('\Pinepain\SimpleRouting\CompiledRoute', $res);
        $this->assertEquals('static([^/]+)', $res->getRegex());
        $this->assertSame(['param' => false], $res->getVariables());

        $res = $compiler->compile(['static', ['param', 'format', false, false]]);
        $this->assertInstanceOf('\Pinepain\SimpleRouting\CompiledRoute', $res);
        $this->assertEquals('static(format)', $res->getRegex());
        $this->assertSame(['param' => false], $res->getVariables());

        $res = $compiler->compile(['static', ['param', 'format', false, '/']]);
        $this->assertInstanceOf('\Pinepain\SimpleRouting\CompiledRoute', $res);
        $this->assertEquals('static/(format)', $res->getRegex());
        $this->assertSame(['param' => false], $res->getVariables());

        $res = $compiler->compile(['static', ['param', 'format', null, false]]);
        $this->assertInstanceOf('\Pinepain\SimpleRouting\CompiledRoute', $res);
        $this->assertEquals('static(format)?', $res->getRegex());
        $this->assertSame(['param' => null], $res->getVariables());

        $res = $compiler->compile(['static', ['param', 'format', null, '/']]);
        $this->assertInstanceOf('\Pinepain\SimpleRouting\CompiledRoute', $res);
        $this->assertEquals('static(?:/(format))?', $res->getRegex());
        $this->assertSame(['param' => null], $res->getVariables());

    }


    public function provideValidateFormatFailureFormat()
    {
        return [
            ['\w)\w'],
            ['\w)(\w)'],
            ['\w(\w'],
            ['\w[\w'],
            ['+'],
            ['?'],
            ['+?'],
            ['+++'],
        ];
    }

    public function provideValidateFormatFailureCapturing()
    {
        return [
            ['()'],
            ['(test)'],
            ['(?<xxx>test)'],
            ['(foo)'],
            ['(?<foo>test)'],
        ];
    }
}
