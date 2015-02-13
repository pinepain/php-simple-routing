<?php


namespace Pinepain\SimpleRouting\Tests\Parser;

use Pinepain\SimpleRouting\Parser;

class ParserTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Parser
     */
    private $parser;

    protected function setUp()
    {
        parent::setUp();

        $this->parser = new Parser();
    }

    /**
     * @covers Pinepain\SimpleRouting\Parser::lintChunk
     */
    public function testLintChunk()
    {
        $parser = $this->parser;

        $this->assertEquals('no-spaces', $parser->lintChunk(' no-spaces  '));
        $this->assertEquals('no-repeated/slashes', $parser->lintChunk('no-repeated/////slashes'));
        $this->assertEquals('/final/chunk/without/slash', $parser->lintChunk('/final/chunk/without/slash/', true));
        $this->assertEquals('/placeholders/{are}/ok', $parser->lintChunk('/placeholders/{are}/ok'));
    }

    /**
     * @covers Pinepain\SimpleRouting\Parser::getChunk
     */
    public function testGetChunk()
    {
        $parser = $this->parser;

        //$parser->shouldReceive('lintChunk')->andReturnUsing(function ($arg) {return $arg;});

        $this->assertEquals('chunk', $parser->getChunk('/test/chunk/string', 6, 11));
        $this->assertEquals('\~quoted\~', $parser->getChunk('/test/~quoted~/string', 6, 14));

    }


    /**
     * @covers Pinepain\SimpleRouting\Parser::parse
     */
    public function testParse()
    {
        $parser = $this->parser;

        $this->assertEquals(['/i/am/static'], $parser->parse('/i/am/static/'));

        $this->assertSame(
            [
                '/with/',
                ['parameter', false, false, false]
            ],
            $parser->parse('/with/{parameter}')
        );

        $this->assertSame(
            [
                '/with/',
                ['parameter', false, false, false],
                '/and_tail'
            ],
            $parser->parse('/with/{parameter}/and_tail')
        );

        $this->assertSame(
            [
                '/with/',
                ['one', false, false, false],
                '/and/',
                ['two', false, false, false],
            ],
            $parser->parse('/with/{one}/and/{two}')
        );

        $this->assertSame(
            [
                '/with',
                ['delimiter', false, false, '/']
            ],
            $parser->parse('/with{/delimiter}')
        );

        $this->assertSame(
            [
                '/with/',
                ['optional', false, null, false]
            ],
            $parser->parse('/with/{optional?}')
        );

        $this->assertSame(
            [
                '/with',
                ['optional_delimiter', false, null, '/']
            ],
            $parser->parse('/with{/optional_delimiter?}')
        );

        $this->assertSame(
            [
                '/with/',
                ['default', false, 'value', false]
            ],
            $parser->parse('/with/{default?value}')
        );

        $this->assertSame(
            [
                '/with/',
                ['format', 'regex_or_name', false, false]
            ],
            $parser->parse('/with/{format:regex_or_name}')
        );

        $this->assertSame(
            [
                '/with/',
                ['format', '[a-z]{2,8}', false, false]
            ],
            $parser->parse('/with/{format:[a-z]{2,8}}')
        );

    }

    /**
     * @covers                   Pinepain\SimpleRouting\Parser::parse
     *
     * @expectedException \Pinepain\SimpleRouting\Exception
     * @expectedExceptionMessage Variable 'fail' already defined at offset 8
     *
     */
    public function testParseFailureAlreadyDefined()
    {
        $parser = $this->parser;

        $parser->parse('/should/{fail}/{fail}');
    }
}
