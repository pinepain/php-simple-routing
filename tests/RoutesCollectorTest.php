<?php


namespace Pinepain\SimpleRouting\Tests;

use Pinepain\SimpleRouting\Chunks\DynamicChunk;
use Pinepain\SimpleRouting\Chunks\StaticChunk;
use Pinepain\SimpleRouting\Parser;
use Pinepain\SimpleRouting\Route;
use Pinepain\SimpleRouting\RoutesCollector;

class RoutesCollectorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var RoutesCollector | \PHPUnit_Framework_MockObject_MockObject
     */
    private $collector;

    protected function setUp()
    {
        parent::setUp();

        /** @var Parser | \PHPUnit_Framework_MockObject_MockObject $parser */
        $parser = $this->getMock(Parser::class);

        $this->collector = new RoutesCollector($parser);
    }

    /**
     * @covers \Pinepain\SimpleRouting\RoutesCollector::isStatic
     */
    public function testIsStatic()
    {
        $collector = $this->collector;

        $this->assertTrue($collector->isStatic([new StaticChunk('static')]));
        $this->assertFalse($collector->isStatic([new StaticChunk('static'), new DynamicChunk('dynamic')]));

        // We doesn't check whether all parts are static, so
        $this->assertFalse($collector->isStatic([new StaticChunk('static'), new StaticChunk('static too')]));
    }

    /**
     * @covers \Pinepain\SimpleRouting\RoutesCollector::__construct
     * @covers \Pinepain\SimpleRouting\RoutesCollector::add
     * @covers \Pinepain\SimpleRouting\RoutesCollector::getStaticRoutes
     * @covers \Pinepain\SimpleRouting\RoutesCollector::getDynamicRoutes
     */
    public function testAddStaticRoute()
    {
        /** @var Parser | \PHPUnit_Framework_MockObject_MockObject $parser */
        $parser = $this->getMock(Parser::class);

        $parser->expects($this->any())
               ->method('parse')
               ->with($this->stringContains('test'))
               ->willReturnCallback(function ($route) {
                   return [new StaticChunk('parsed ' . $route)];
               });

        $collector = new RoutesCollector($parser);

        $this->assertEquals([new StaticChunk('parsed test route 1')], $collector->add('test route 1', 'test handler 1'));
        $this->assertEquals([new StaticChunk('parsed test route 2')], $collector->add('test route 2', 'test handler 2'));
        $this->assertEquals([new StaticChunk('parsed test route 3')], $collector->add('test route 3', 'test handler 3'));


        $static_routes = [
            'test route 1' => new Route('test handler 1', [new StaticChunk('parsed test route 1')]),
            'test route 2' => new Route('test handler 2', [new StaticChunk('parsed test route 2')]),
            'test route 3' => new Route('test handler 3', [new StaticChunk('parsed test route 3')]),
        ];

        $this->assertEquals($static_routes, $collector->getStaticRoutes());
        $this->assertEquals([], $collector->getDynamicRoutes());
    }


    /**
     * @covers \Pinepain\SimpleRouting\RoutesCollector::__construct
     * @covers \Pinepain\SimpleRouting\RoutesCollector::add
     * @covers \Pinepain\SimpleRouting\RoutesCollector::getStaticRoutes
     * @covers \Pinepain\SimpleRouting\RoutesCollector::getDynamicRoutes
     */
    public function testAddDynamicRoute()
    {
        /** @var Parser | \PHPUnit_Framework_MockObject_MockObject $parser */
        $parser = $this->getMock(Parser::class);

        $parser
            ->expects($this->atLeast(1))
            ->method('parse')
            ->with($this->stringContains('test'))
            ->willReturnCallback(function ($route) {
                return ['dynamic', $route];
            });

        $collector = new RoutesCollector($parser);


        $this->assertEquals(['dynamic', 'test route 1'], $collector->add('test route 1', 'test handler 1'));
        $this->assertEquals(['dynamic', 'test route 2'], $collector->add('test route 2', 'test handler 2'));
        $this->assertEquals(['dynamic', 'test route 3'], $collector->add('test route 3', 'test handler 3'));
        $this->assertEquals(['dynamic', '/test-route-4'], $collector->add('/test-route-4', 'test handler 4'));


        $dynamic_routes = [
            'test route 1'  => new Route('test handler 1', ['dynamic', 'test route 1']),
            'test route 2'  => new Route('test handler 2', ['dynamic', 'test route 2']),
            'test route 3'  => new Route('test handler 3', ['dynamic', 'test route 3']),
            '/test-route-4' => new Route('test handler 4', ['dynamic', '/test-route-4']),
        ];

        $this->assertEquals([], $collector->getStaticRoutes());
        $this->assertEquals($dynamic_routes, $collector->getDynamicRoutes());
    }

    /**
     * @covers                   \Pinepain\SimpleRouting\RoutesCollector::add
     *
     * @expectedException \Pinepain\SimpleRouting\Exception
     * @expectedExceptionMessage Route 'test' already registered
     */
    public function testAddDuplicateFailure()
    {
        /** @var Parser | \PHPUnit_Framework_MockObject_MockObject $parser */
        $parser = $this->getMock(Parser::class);

        $parser
            ->expects($this->once())
            ->method('parse')
            ->willReturn([new StaticChunk('parsed as static')]);

        $collector = new RoutesCollector($parser);

        $collector->add('test', 'handler');
        $collector->add('test', 'handler');
    }
}
