<?php


namespace Pinepain\SimpleRouting\Tests\Parser;

use Pinepain\SimpleRouting\Chunks\DynamicChunk;
use Pinepain\SimpleRouting\Chunks\StaticChunk;
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
     * @covers \Pinepain\SimpleRouting\Parser::lintChunk
     */
    public function testLintChunk()
    {
        $parser = $this->parser;

        $this->assertEquals('no-spaces', $parser->lintChunk(' no-spaces  '));
        $this->assertEquals('no-repeated/slashes', $parser->lintChunk('no-repeated/////slashes'));
        $this->assertEquals('/final/chunk/without/slash', $parser->lintChunk('/final/chunk/without/slash', true));
        $this->assertEquals('/final/chunk/with/slash/', $parser->lintChunk('/final/chunk/with/slash/', true));
        $this->assertEquals('/placeholders/{are}/ok', $parser->lintChunk('/placeholders/{are}/ok'));
    }

    /**
     * @covers \Pinepain\SimpleRouting\Parser::getChunk
     */
    public function testGetChunk()
    {
        $parser = $this->parser;

        //$parser->shouldReceive('lintChunk')->andReturnUsing(function ($arg) {return $arg;});

        $this->assertEquals('chunk', $parser->getChunk('/test/chunk/string', 6, 11));
        $this->assertEquals('~quoted~', $parser->getChunk('/test/~quoted~/string', 6, 14));

    }


    /**
     * @covers \Pinepain\SimpleRouting\Parser::parse
     */
    public function testParse()
    {
        $parser = $this->parser;

        $this->assertEquals([new StaticChunk('/i/am/static')], $parser->parse('/i/am/static'));
        $this->assertEquals([new StaticChunk('/i-am-dashed')], $parser->parse('/i-am-dashed'));

        $this->assertEquals(
            [
                new StaticChunk('/with/'),
                new DynamicChunk('parameter'),
            ],
            $parser->parse('/with/{parameter}')
        );

        $this->assertEquals(
            [
                new StaticChunk('/with/'),
                new DynamicChunk('parameter-with-dashes'),
            ],
            $parser->parse('/with/{parameter-with-dashes}')
        );

        $this->assertEquals(
            [
                new StaticChunk('/with/'),
                new DynamicChunk('parameter'),
                new StaticChunk('/and_tail'),
            ],
            $parser->parse('/with/{parameter}/and_tail')
        );

        $this->assertEquals(
            [
                new StaticChunk('/with/'),
                new DynamicChunk('one'),
                new StaticChunk('/and/'),
                new DynamicChunk('two'),
            ],
            $parser->parse('/with/{one}/and/{two}')
        );

        $this->assertEquals(
            [
                new StaticChunk('/with'),
                new DynamicChunk('delimiter', false, false, '/'),
            ],
            $parser->parse('/with{/delimiter}')
        );

        $this->assertEquals(
            [
                new StaticChunk('/with'),
                new DynamicChunk('delimiter', false, false, false, '/'),
            ],
            $parser->parse('/with{delimiter/}')
        );

        $this->assertEquals(
            [
                new StaticChunk('/with'),
                new DynamicChunk('delimiter', false, false, '/', '/'),
            ],
            $parser->parse('/with{/delimiter/}')
        );

        $this->assertEquals(
            [
                new StaticChunk('/with'),
                new DynamicChunk('delimiter', false, false, '/', '/'),
                new StaticChunk('test'),
            ],
            $parser->parse('/with{/delimiter/}test')
        );

        $this->assertEquals(
            [
                new StaticChunk('/with/'),
                new DynamicChunk('optional', false, null),
            ],
            $parser->parse('/with/{optional?}')
        );

        $this->assertEquals(
            [
                new StaticChunk('/with'),
                new DynamicChunk('optional_delimiter', false, null, '/'),
            ],
            $parser->parse('/with{/optional_delimiter?}')
        );

        $this->assertEquals(
            [
                new StaticChunk('/with/'),
                new DynamicChunk('optional_delimiter', false, null, false, '/'),
            ],
            $parser->parse('/with/{optional_delimiter/?}')
        );

        $this->assertEquals(
            [
                new StaticChunk('/with/'),
                new DynamicChunk('default', false, 'value'),
            ],
            $parser->parse('/with/{default?value}')
        );

        $this->assertEquals(
            [
                new StaticChunk('/with/'),
                new DynamicChunk('format', 'regex_or_name'),
            ],
            $parser->parse('/with/{format:regex_or_name}')
        );

        $this->assertEquals(
            [
                new StaticChunk('/with/'),
                new DynamicChunk('format', '[a-z]{2,8}'),
            ],
            $parser->parse('/with/{format:[a-z]{2,8}}')
        );

        $this->assertEquals(
            [
                new StaticChunk('/with/'),
                new DynamicChunk('param'),
                new StaticChunk('/static'),
                new DynamicChunk('inside', false, false, '/'),
            ],
            $parser->parse('/with/{param}/static{/inside}')
        );

        $this->assertEquals(
            [
                new StaticChunk('/with/'),
                new DynamicChunk('params'),
                new DynamicChunk('one'),
                new DynamicChunk('by'),
                new DynamicChunk('one_', false, false, '/'),
            ],
            $parser->parse('/with/{params}{one}{by}{/one_}')
        );
    }

    /**
     * @covers \Pinepain\SimpleRouting\Parser::parse
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
