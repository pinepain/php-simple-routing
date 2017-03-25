<?php


namespace Pinepain\SimpleRouting\Tests;

use Mockery as m;
use Pinepain\SimpleRouting\Chunks\DynamicChunk;
use Pinepain\SimpleRouting\CompiledRoute;
use Pinepain\SimpleRouting\Compiler;
use Pinepain\SimpleRouting\Contracts\CompilerFilterInterface;
use Pinepain\SimpleRouting\Route;
use Pinepain\SimpleRouting\RulesGenerator;

class RulesGeneratorTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @covers \Pinepain\SimpleRouting\RulesGenerator::getApproxChunkSize
     */
    public function testGetApproxChunksSize()
    {
        /** @var CompilerFilterInterface | \PHPUnit_Framework_MockObject_MockObject $filter */
        $filter = $this->getMockBuilder(CompilerFilterInterface::class)
                       ->disableOriginalConstructor()
                       ->getMock();


        /** @var Compiler | \PHPUnit_Framework_MockObject_MockObject $compiler */
        $compiler = $this->getMockBuilder(Compiler::class)
                         ->disableOriginalConstructor()
                         ->getMock();

        $generator = new RulesGenerator($filter, $compiler);

        $this->assertEquals(10, $generator->getApproxChunkSize());
    }

    /**
     * @covers \Pinepain\SimpleRouting\RulesGenerator::calcChunkSize
     */
    public function testCalcChunkSize()
    {
        /** @var RulesGenerator | \PHPUnit_Framework_MockObject_MockObject $generator */
        $generator = $this->getMockBuilder(RulesGenerator::class)
                          ->disableOriginalConstructor()
                          ->setMethods(['getApproxChunkSize'])
                          ->getMock();

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
     * @covers \Pinepain\SimpleRouting\RulesGenerator::generate
     */
    public function testGenerate()
    {
        /** @var RulesGenerator | \PHPUnit_Framework_MockObject_MockObject $generator */
        $generator = $this->getMockBuilder(RulesGenerator::class)
                          ->disableOriginalConstructor()
                          ->setMethods(['calcChunkSize', 'generateChunk'])
                          ->getMock();

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
     * @covers \Pinepain\SimpleRouting\RulesGenerator::__construct
     * @covers \Pinepain\SimpleRouting\RulesGenerator::generateChunk
     */
    public function testGenerateChunk()
    {
        $optional_compiled  = new CompiledRoute('optional-regex', ['test' => 'optional'], true);
        $mandatory_compiled = new CompiledRoute('mandatory-regex', ['test' => 'boolean false'], false);

        /** @var CompilerFilterInterface | \PHPUnit_Framework_MockObject_MockObject $filter */
        $filter = $this->getMockBuilder(CompilerFilterInterface::class)
                       ->setMethods(['filter'])
                       ->disableOriginalConstructor()
                       ->getMock();

        $filter->expects($this->any())
               ->method('filter')
               ->willReturnArgument(0);

        /** @var Compiler | \PHPUnit_Framework_MockObject_MockObject $compiler */
        $compiler = $this->getMockBuilder(Compiler::class)
                         ->setMethods(['compile'])
                         ->disableOriginalConstructor()
                         ->getMock();

        $compiler->expects($this->exactly(4))
                 ->method('compile')
                 ->willReturnOnConsecutiveCalls(
                     $mandatory_compiled,
                     $optional_compiled,
                     $mandatory_compiled, $optional_compiled
                 );

        $generator = new RulesGenerator($filter, $compiler);

        $this->assertEquals([], $generator->generate([]));

        $mandatory_expected = [
            "~^(?|mandatory-regex)$~",
            [
                2 => [
                    'handler',
                    ['test' => 'boolean false'],
                ],
            ],
        ];

        $this->assertEquals($mandatory_expected, $generator->generateChunk([
            'route' => new Route('handler', [new DynamicChunk('parsed mandatory')]),
        ]));

        $optional_expected = [
            "~^(?|optional-regex())$~",
            [
                3 => [
                    'handler',
                    ['test' => 'optional'],
                ],
            ],
        ];

        $this->assertEquals($optional_expected, $generator->generateChunk([
            'route' => new Route('handler', [new DynamicChunk('parsed optional')]),
        ]));


        $optional_and_mandatory_expected = [
            "~^(?|mandatory-regex|optional-regex())$~",
            [
                2 => [
                    'handler',
                    ['test' => 'boolean false'],
                ],
                3 => [
                    'handler',
                    ['test' => 'optional'],
                ],

            ],
        ];

        $this->assertEquals(
            $optional_and_mandatory_expected,
            $generator->generateChunk([
                    'route-mandatory' => new Route('handler', [new DynamicChunk('parsed mandatory')]),
                    'route-optional'  => new Route('handler', [new DynamicChunk('parsed optional')]),
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
