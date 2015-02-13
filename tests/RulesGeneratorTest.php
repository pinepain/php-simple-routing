<?php


namespace Pinepain\SimpleRouting\Tests;

use Mockery as m;
use Pinepain\SimpleRouting\RulesGenerator;

class RulesGeneratorTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @covers Pinepain\SimpleRouting\RulesGenerator::getApproxChunkSize
     */
    public function testGetApproxChunksSize()
    {
        $generator = new RulesGenerator(null, null);

        $this->assertEquals(10, $generator->getApproxChunkSize());
    }

    /**
     * @covers Pinepain\SimpleRouting\RulesGenerator::calcChunkSize
     */
    public function testCalcChunkSize()
    {
        /** @var \Pinepain\SimpleRouting\RulesGenerator | \PHPUnit_Framework_MockObject_MockObject $generator */
        $generator = $this->getMock('Pinepain\SimpleRouting\RulesGenerator', ['getApproxChunkSize'], [null, null]);

        $generator->expects($this->any())
            ->method('getApproxChunkSize')
            ->willReturnOnConsecutiveCalls(1, 1, 1, 2, 2, 2, 5, 5, 5, 5, 10, 10, 10, 10, 10, 10, 10);

        $this->assertEquals(0, $generator->calcChunkSize(0));
        $this->assertEquals(1, $generator->calcChunkSize(1));
        $this->assertEquals(1, $generator->calcChunkSize(100));

        $this->assertEquals(0, $generator->calcChunkSize(0));
        $this->assertEquals(2, $generator->calcChunkSize(2));
        $this->assertEquals(2, $generator->calcChunkSize(100));

        $this->assertEquals(0, $generator->calcChunkSize(0));
        $this->assertEquals(5, $generator->calcChunkSize(5));
        $this->assertEquals(5, $generator->calcChunkSize(100));

        $this->assertEquals(0, $generator->calcChunkSize(0));
        $this->assertEquals(5, $generator->calcChunkSize(5));
        $this->assertEquals(10, $generator->calcChunkSize(10));
        $this->assertEquals(11, $generator->calcChunkSize(11));
        $this->assertEquals(8, $generator->calcChunkSize(15));
        $this->assertEquals(9, $generator->calcChunkSize(17));
        $this->assertEquals(10, $generator->calcChunkSize(20));
        $this->assertEquals(10, $generator->calcChunkSize(100));
    }

    /**
     * @covers Pinepain\SimpleRouting\RulesGenerator::generate
     */
    public function testGenerate()
    {
        /** @var \Pinepain\SimpleRouting\RulesGenerator | \PHPUnit_Framework_MockObject_MockObject $generator */
        $generator = $this->getMock('Pinepain\SimpleRouting\RulesGenerator', ['calcChunkSize', 'generateChunk'],
            [null, null]);

        $generator->expects($this->any())
            ->method('calcChunkSize')
            ->willReturn(10);

        $generator->expects($this->any())
            ->method('generateChunk')
            ->willReturn(['generated-regex', ['routes', 'map']]);


        $this->assertSame([], $generator->generate([]));
        $this->assertSame([['generated-regex', ['routes', 'map']]],
            $generator->generate(array_fill(0, 5, null)));

        $this->assertSame([['generated-regex', ['routes', 'map']]],
            $generator->generate(array_fill(0, 10, null)));

        $this->assertSame([
            ['generated-regex', ['routes', 'map']],
            ['generated-regex', ['routes', 'map']],
        ], $generator->generate(array_fill(0, 11, null)));

        $this->assertSame([
            ['generated-regex', ['routes', 'map']],
            ['generated-regex', ['routes', 'map']],
        ], $generator->generate(array_fill(0, 20, null)));

        $this->assertSame([
            ['generated-regex', ['routes', 'map']],
            ['generated-regex', ['routes', 'map']],
            ['generated-regex', ['routes', 'map']],
        ], $generator->generate(array_fill(0, 21, null)));
    }

    /**
     * @covers Pinepain\SimpleRouting\RulesGenerator::__construct
     * @covers Pinepain\SimpleRouting\RulesGenerator::generateChunk
     */
    public function testGenerateChunk()
    {
        $optional_compiled = m::mock('stdClass');
        $optional_compiled->shouldReceive('getVariables')->andReturn(['test' => 'optional']);
        $optional_compiled->shouldReceive('hasOptional')->andReturn(true);
        $optional_compiled->shouldReceive('getRegex')->andReturn('optional-regex');

        $mandatory_compiled = m::mock('stdClass');
        $mandatory_compiled->shouldReceive('getVariables')->andReturn(['test' => 'boolean false']);
        $mandatory_compiled->shouldReceive('hasOptional')->andReturn(false);
        $mandatory_compiled->shouldReceive('getRegex')->andReturn('mandatory-regex');


        $filter = m::mock('stdClass');
        $filter->shouldReceive('filter')->with(['parsed optional'])->andReturn(['parsed and filtered optional']);
        $filter->shouldReceive('filter')->with(['parsed mandatory'])->andReturn(['parsed and filtered mandatory']);


        $compiler = m::mock('stdClass');
        $compiler->shouldReceive('compile')->with(['parsed and filtered optional'])->andReturn($optional_compiled);
        $compiler->shouldReceive('compile')->with(['parsed and filtered mandatory'])->andReturn($mandatory_compiled);

        $generator = new RulesGenerator($filter, $compiler);

        $this->assertEquals([], $generator->generate([]));

        $mandatory_expected = [
            "~^(?|mandatory-regex)$~",
            [
                2 => [
                    'handler',
                    ['test' => 'boolean false'],
                ]
            ]
        ];

        $this->assertEquals($mandatory_expected,
            $generator->generateChunk(['route' => ['handler', ['parsed mandatory']]]));

        $optional_expected = [
            "~^(?|optional-regex())$~",
            [
                3 => [
                    'handler',
                    ['test' => 'optional'],
                ]
            ]
        ];

        $this->assertEquals($optional_expected,
            $generator->generateChunk(['route' => ['handler', ['parsed optional']]]));


        $optional_and_mandatory_expected = [
            "~^(?|optional-regex()|mandatory-regex()())$~",
            [
                3 => [
                    'handler',
                    ['test' => 'optional'],
                ],
                4 => [
                    'handler',
                    ['test' => 'boolean false'],
                ]

            ]
        ];

        $this->assertEquals(
            $optional_and_mandatory_expected,
            $generator->generateChunk([
                    'route-optional'  => ['handler', ['parsed optional']],
                    'route-mandatory' => ['handler', ['parsed mandatory']],
                ]
            )
        );

    }

    protected function tearDown()
    {
        parent::tearDown();
        m::close();
    }


}
