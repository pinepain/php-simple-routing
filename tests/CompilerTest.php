<?php


namespace Pinepain\SimpleRouting\Tests;

use PHPUnit\Framework\TestCase;
use Pinepain\SimpleRouting\Chunks\DynamicChunk;
use Pinepain\SimpleRouting\Chunks\StaticChunk;
use Pinepain\SimpleRouting\CompiledRoute;
use Pinepain\SimpleRouting\Compiler;

class CompilerTest extends TestCase
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

        $this->assertNull($compiler->validateFormat('name', 'format'));
        $this->assertNull($compiler->validateFormat('name', 'test'));
    }

    /**
     * @covers       \Pinepain\SimpleRouting\Compiler::validateFormat
     *
     * @expectedException \Pinepain\SimpleRouting\Exception
     * @expectedExceptionMessage Invalid format regex
     *
     * @dataProvider provideValidateFormatFailureFormat
     *
     * @param $regex
     */
    public function testValidateFormatFailureFormat($regex)
    {
        $compiler = $this->compiler;

        $compiler->validateFormat('name', $regex);
    }

    /**
     * @covers       \Pinepain\SimpleRouting\Compiler::validateFormat
     *
     * @expectedException \Pinepain\SimpleRouting\Exception
     * @expectedExceptionMessage Catching groups in regex
     *
     * @dataProvider provideValidateFormatFailureCapturing
     *
     * @param $regex
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

        $res = $compiler->compile([new StaticChunk('static')]);
        $this->assertInstanceOf(CompiledRoute::class, $res);
        $this->assertEquals('static', $res->getRegex());
        $this->assertSame([], $res->getVariables());

        $res = $compiler->compile([new StaticChunk('/i-am-dashed')]);
        $this->assertInstanceOf(CompiledRoute::class, $res);
        $this->assertEquals('/i\-am\-dashed', $res->getRegex());
        $this->assertSame([], $res->getVariables());

        $res = $compiler->compile([new StaticChunk('static'), new DynamicChunk('param')]);
        $this->assertInstanceOf(CompiledRoute::class, $res);
        $this->assertEquals('static([^/]+)', $res->getRegex());
        $this->assertSame(['param' => false], $res->getVariables());

        $res = $compiler->compile([new StaticChunk('/i-am-dashed'), new DynamicChunk('param')]);
        $this->assertInstanceOf(CompiledRoute::class, $res);
        $this->assertEquals('/i\-am\-dashed([^/]+)', $res->getRegex());
        $this->assertSame(['param' => false], $res->getVariables());

        $res = $compiler->compile([new StaticChunk('static'), new DynamicChunk('param', 'format')]);
        $this->assertInstanceOf(CompiledRoute::class, $res);
        $this->assertEquals('static(format)', $res->getRegex());
        $this->assertSame(['param' => false], $res->getVariables());

        $res = $compiler->compile([new StaticChunk('static'), new DynamicChunk('param', 'format', false, '/')]);
        $this->assertInstanceOf(CompiledRoute::class, $res);
        $this->assertEquals('static/(format)', $res->getRegex());
        $this->assertSame(['param' => false], $res->getVariables());

        $res = $compiler->compile([new StaticChunk('static'), new DynamicChunk('param', 'format', false, '/'), new StaticChunk('/test')]);
        $this->assertInstanceOf(CompiledRoute::class, $res);
        $this->assertEquals('static/(format)/test', $res->getRegex());
        $this->assertSame(['param' => false], $res->getVariables());

        $res = $compiler->compile([new StaticChunk('static'), new DynamicChunk('param', 'format', false, false, '/')]);
        $this->assertInstanceOf(CompiledRoute::class, $res);
        $this->assertEquals('static(format)/', $res->getRegex());
        $this->assertSame(['param' => false], $res->getVariables());

        $res = $compiler->compile([new StaticChunk('static'), new DynamicChunk('param', 'format', false, '/', '/'), new StaticChunk('test')]);
        $this->assertInstanceOf(CompiledRoute::class, $res);
        $this->assertEquals('static/(format)/test', $res->getRegex());
        $this->assertSame(['param' => false], $res->getVariables());

        $res = $compiler->compile([new StaticChunk('static'), new DynamicChunk('param', 'format', null)]);
        $this->assertInstanceOf(CompiledRoute::class, $res);
        $this->assertEquals('static(format)?', $res->getRegex());
        $this->assertSame(['param' => null], $res->getVariables());

        $res = $compiler->compile([new StaticChunk('static'), new DynamicChunk('param', 'format', null, '/')]);
        $this->assertInstanceOf(CompiledRoute::class, $res);
        $this->assertEquals('static(?:/(format))?', $res->getRegex());
        $this->assertSame(['param' => null], $res->getVariables());

        $res = $compiler->compile([new StaticChunk('static'), new DynamicChunk('param', 'format', null, false, '/')]);
        $this->assertInstanceOf(CompiledRoute::class, $res);
        $this->assertEquals('static(?:(format)/)?', $res->getRegex());
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
