<?php


namespace Pinepain\SimpleRouting\Tests;

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

        $parser = $this->getMock('Pinepain\SimpleRouting\Parser');

        $this->collector = new RoutesCollector($parser);
    }

    /**
     * @covers \Pinepain\SimpleRouting\RoutesCollector::isStatic
     */
    public function testIsStatic()
    {
        $collector = $this->collector;

        $this->assertTrue($collector->isStatic(['static']));
        $this->assertFalse($collector->isStatic(['static', ['dynamic']]));

        // We doesn't check whether all parts are static, so
        $this->assertFalse($collector->isStatic(['static', 'static too']));
    }

    /**
     * @covers \Pinepain\SimpleRouting\RoutesCollector::__construct
     * @covers \Pinepain\SimpleRouting\RoutesCollector::add
     * @covers \Pinepain\SimpleRouting\RoutesCollector::getStaticRoutes
     * @covers \Pinepain\SimpleRouting\RoutesCollector::getDynamicRoutes
     */
    public function testAddStaticRoute()
    {
        $parser = $this->getMock('Pinepain\SimpleRouting\Parser');

        $parser
            ->expects($this->any())
            ->method('parse')
            ->with($this->stringContains('test'))
            ->willReturnCallback(function ($route) {
                return ['parsed ' . $route];
            });

        //$this->collector = $this->getMock('Pinepain\SimpleRouting\RoutesCollector', array(), [$parser]); //new RoutesCollector($parser);
        $collector = new RoutesCollector($parser);


        $this->assertEquals(['parsed test route 1'], $collector->add('test route 1', 'test handler 1'));
        $this->assertEquals(['parsed test route 2'], $collector->add('test route 2', 'test handler 2'));
        $this->assertEquals(['parsed test route 3'], $collector->add('test route 3', 'test handler 3'));


        $static_routes = [
            'test route 1' => ['test handler 1', ['parsed test route 1']],
            'test route 2' => ['test handler 2', ['parsed test route 2']],
            'test route 3' => ['test handler 3', ['parsed test route 3']],
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
        $parser = $this->getMock('Pinepain\SimpleRouting\Parser');

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


        $dynamic_routes = [
            'test route 1' => ['test handler 1', ['dynamic', 'test route 1']],
            'test route 2' => ['test handler 2', ['dynamic', 'test route 2']],
            'test route 3' => ['test handler 3', ['dynamic', 'test route 3']],
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
        $parser = $this->getMock('Pinepain\SimpleRouting\Parser');

        $parser
            ->expects($this->once())
            ->method('parse')
            ->willReturn(array('parsed as static'));

        $collector = new RoutesCollector($parser);

        $collector->add('test', 'handler');
        $collector->add('test', 'handler');
    }
}
