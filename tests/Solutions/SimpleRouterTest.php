<?php


namespace Pinepain\SimpleRouting\Tests\Solutions;


use Pinepain\SimpleRouting\Matcher;
use Pinepain\SimpleRouting\RoutesCollector;
use Pinepain\SimpleRouting\RulesGenerator;
use Pinepain\SimpleRouting\Solutions\SimpleRouter;
use Pinepain\SimpleRouting\UrlGenerator;

class SimpleRouterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers \Pinepain\SimpleRouting\Solutions\SimpleRouter::__construct
     * @covers \Pinepain\SimpleRouting\Solutions\SimpleRouter::add
     */
    public function testAdd()
    {
        /** @var RoutesCollector | \PHPUnit_Framework_MockObject_MockObject $collector */
        $collector = $this->getMockBuilder(RoutesCollector::class)
                          ->setMethods(['add'])
                          ->disableOriginalConstructor()
                          ->getMock();

        /** @var RulesGenerator | \PHPUnit_Framework_MockObject_MockObject $generator */
        $generator = $this->getMockBuilder(RulesGenerator::class)
                          ->disableOriginalConstructor()
                          ->getMock();

        /** @var Matcher | \PHPUnit_Framework_MockObject_MockObject $matcher */
        $matcher = $this->getMockBuilder(Matcher::class)
                        ->disableOriginalConstructor()
                        ->getMock();

        /** @var UrlGenerator | \PHPUnit_Framework_MockObject_MockObject $url_generator */
        $url_generator = $this->getMockBuilder(UrlGenerator::class)
                              ->disableOriginalConstructor()
                              ->getMock();

        $collector->expects($this->at(0))
                  ->method('add')
                  ->with('route-1', 'handler-1')
                  ->willReturn('parsed-1');

        $collector->expects($this->at(1))
                  ->method('add')
                  ->with('route-2', 'handler-2')
                  ->willReturn('parsed-2');


        $router = new SimpleRouter($collector, $generator, $matcher, $url_generator);

        $this->assertEquals('parsed-1', $router->add('route-1', 'handler-1'));
        $this->assertEquals('parsed-2', $router->add('route-2', 'handler-2'));
    }

    /**
     * @covers \Pinepain\SimpleRouting\Solutions\SimpleRouter::match
     */
    public function testDispatch()
    {
        /** @var RoutesCollector | \PHPUnit_Framework_MockObject_MockObject $collector */
        $collector = $this->getMockBuilder(RoutesCollector::class)
                          ->setMethods(['getStaticRoutes', 'getDynamicRoutes'])
                          ->disableOriginalConstructor()
                          ->getMock();

        $collector->expects($this->exactly(2))
                  ->method('getStaticRoutes')
                  ->with()
                  ->willReturnOnConsecutiveCalls(['static-routes-1'], ['static-routes-2']);

        $collector->expects($this->exactly(2))
                  ->method('getDynamicRoutes')
                  ->with()
                  ->willReturnOnConsecutiveCalls(['dynamic-routes-1'], ['dynamic-routes-2']);

        /** @var RulesGenerator | \PHPUnit_Framework_MockObject_MockObject $generator */
        $generator = $this->getMockBuilder(RulesGenerator::class)
                          ->setMethods(['generate'])
                          ->disableOriginalConstructor()
                          ->getMock();

        $generator->expects($this->exactly(2))
                  ->method('generate')
                  ->withConsecutive([['dynamic-routes-1']], [['dynamic-routes-2']])
                  ->willReturnOnConsecutiveCalls(['dynamic-generated-rules-1'], ['dynamic-generated-rules-2']);

        /** @var Matcher | \PHPUnit_Framework_MockObject_MockObject $matcher */
        $matcher = $this->getMockBuilder(Matcher::class)
                        ->setMethods(['setStaticRules', 'setDynamicRules', 'match'])
                        ->disableOriginalConstructor()
                        ->getMock();

        $matcher->expects($this->exactly(2))
                ->method('setStaticRules')
                ->withConsecutive([['static-routes-1']], [['static-routes-2']]);

        $matcher->expects($this->exactly(2))
                ->method('setDynamicRules')
                ->withConsecutive([['dynamic-generated-rules-1']], [['dynamic-generated-rules-2']]);

        $matcher->expects($this->exactly(2))
                ->method('match')
                ->withConsecutive(['url-1'], ['url-2'])
                ->willReturnOnConsecutiveCalls(['handler-1', 'variables-1'], ['handler-2', 'variables-2']);

        /** @var UrlGenerator | \PHPUnit_Framework_MockObject_MockObject $url_generator */
        $url_generator = $this->getMockBuilder(UrlGenerator::class)
                              ->disableOriginalConstructor()
                              ->getMock();

        $router = new SimpleRouter($collector, $generator, $matcher, $url_generator);

        $this->assertSame(['handler-1', 'variables-1'], $router->match('url-1'));
        $this->assertSame(['handler-2', 'variables-2'], $router->match('url-2'));
    }

    /**
     * @covers \Pinepain\SimpleRouting\Solutions\SimpleRouter::url
     */
    public function testUrl()
    {
        /** @var RoutesCollector | \PHPUnit_Framework_MockObject_MockObject $collector */
        $collector = $this->getMockBuilder(RoutesCollector::class)
                          ->setMethods(['getStaticRoutes', 'getDynamicRoutes'])
                          ->disableOriginalConstructor()
                          ->getMock();

        /** @var RulesGenerator | \PHPUnit_Framework_MockObject_MockObject $generator */
        $generator = $this->getMockBuilder(RulesGenerator::class)
                          ->disableOriginalConstructor()
                          ->getMock();

        /** @var Matcher | \PHPUnit_Framework_MockObject_MockObject $matcher */
        $matcher = $this->getMockBuilder(Matcher::class)
                        ->disableOriginalConstructor()
                        ->getMock();

        /** @var UrlGenerator | \PHPUnit_Framework_MockObject_MockObject $url_generator */
        $url_generator = $this->getMockBuilder(UrlGenerator::class)
                              ->setMethods(['setMapFromRoutesCollector', 'generate'])
                              ->disableOriginalConstructor()
                              ->getMock();

        $url_generator->expects($this->exactly(3))
                      ->method('setMapFromRoutesCollector')
                      ->with($collector);

        $url_generator->expects($this->exactly(3))
                      ->method('generate')
                      ->withConsecutive(
                          ['test-1', ['test-1'], false],
                          ['test-2', ['test-2'], false],
                          ['test-3', ['test-3'], true]
                      )
                      ->willReturn('url');

        $router = new SimpleRouter($collector, $generator, $matcher, $url_generator);

        $this->assertSame('url', $router->url('test-1', ['test-1']));
        $this->assertSame('url', $router->url('test-2', ['test-2'], false));
        $this->assertSame('url', $router->url('test-3', ['test-3'], true));
    }
}
