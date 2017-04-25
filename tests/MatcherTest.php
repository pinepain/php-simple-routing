<?php declare(strict_types=1);

namespace Pinepain\SimpleRouting\Tests;

use PHPUnit\Framework\TestCase;
use Pinepain\SimpleRouting\Chunk;
use Pinepain\SimpleRouting\Crumb;
use Pinepain\SimpleRouting\Match;
use Pinepain\SimpleRouting\Matcher;
use Pinepain\SimpleRouting\Route;

class MatcherTest extends TestCase
{
    /**
     * @covers \Pinepain\SimpleRouting\Matcher::extractVariablesFromMatches
     */
    public function testExtractVariablesFromMatches()
    {
        $dispatcher = new Matcher([], []);

        $this->assertEquals(
            [
                'first'           => 'first-val',
                'second'          => 'second-val',
                'first-optional'  => 'first-optional-val',
                'second-optional' => 'second-default',
            ],
            $dispatcher->extractVariablesFromMatches(
                ['whole-match', 'first-val', 'second-val', 'first-optional-val'],
                [
                    'first'           => 'bool false',
                    'second'          => 'bool false',
                    'first-optional'  => 'first-default',
                    'second-optional' => 'second-default',
                ]
            )
        );
    }

    /**
     * @covers \Pinepain\SimpleRouting\Matcher::matchDynamicRoute
     */
    public function testMatchDynamicRoute()
    {
        /** @var Matcher | \PHPUnit_Framework_MockObject_MockObject $dispatcher */
        $dispatcher = $this->getMockBuilder(Matcher::class)
                           ->setMethods(['extractVariablesFromMatches'])
                           ->getMock();

        $dispatcher->expects($this->atLeast(1))
                   ->method('extractVariablesFromMatches')
                   ->willReturn(['resolved' => 'vars']);

        $dynamic_rules = [
            new Chunk(
                '~^(?|(a)|(a)(b)|(a)(b)(c))$~',
                [
                    2 => new Crumb('handler-1', ['first vars']),
                    3 => new Crumb('handler-2', ['second vars']),
                    4 => new Crumb('handler-3', ['third vars']),
                ]
            ),
        ];

        $this->assertEquals(
            new Match('handler-2', ['resolved' => 'vars']),
            $dispatcher->matchDynamicRoute($dynamic_rules, 'ab')
        );
    }

    /**
     * @expectedException \Pinepain\SimpleRouting\NotFoundException
     * @expectedExceptionMessage Url 'bar' does not match any route
     */
    public function testMatchDynamicRouteMiss()
    {
        /** @var Matcher | \PHPUnit_Framework_MockObject_MockObject $dispatcher */
        $dispatcher = $this->getMockBuilder(Matcher::class)
                           ->setMethods(['extractVariablesFromMatches'])
                           ->getMock();

        $dispatcher->expects($this->any())->method('extractVariablesFromMatches');

        $dispatcher->matchDynamicRoute([new Chunk('~none~', [])], 'bar');
    }

    /**
     * @expectedException \Pinepain\SimpleRouting\NotFoundException
     * @expectedExceptionMessage Url 'foo' does not match any route
     */
    public function testMatchDynamicRouteMissWithoutRules()
    {
        /** @var Matcher | \PHPUnit_Framework_MockObject_MockObject $dispatcher */
        $dispatcher = $this->getMockBuilder(Matcher::class)
                           ->setMethods(['extractVariablesFromMatches'])
                           ->getMock();

        $dispatcher->expects($this->any())->method('extractVariablesFromMatches');

        $this->assertNull($dispatcher->matchDynamicRoute([], 'foo'));
    }


    /**
     * @covers \Pinepain\SimpleRouting\Matcher::__construct
     * @covers \Pinepain\SimpleRouting\Matcher::match
     * @covers \Pinepain\SimpleRouting\Matcher::setStaticRules
     * @covers \Pinepain\SimpleRouting\Matcher::setDynamicRules
     */
    public function testMatch()
    {
        $static_rules  = [
            'abcd' => new Route('here will be dragons', []),
            'a'    => new Route('static overrides dynamic', []),
        ];
        $dynamic_rules = ['here will be no dragons, but only dynamic rules'];

        /** @var Matcher | \PHPUnit_Framework_MockObject_MockObject $dispatcher */
        $dispatcher = $this->getMockBuilder(Matcher::class)
                           ->setMethods(['matchDynamicRoute'])
                           ->setConstructorArgs([$static_rules, $dynamic_rules])
                           ->getMock();

        $dispatcher->expects($this->at(0))
                   ->method('matchDynamicRoute')
                   ->with($dynamic_rules, 'nonexistent')
                   ->willReturn(null);

        $dispatcher->expects($this->at(1))
                   ->method('matchDynamicRoute')
                   ->with($dynamic_rules, 'ab')
                   ->willReturn(new Match('dynamic dispatched'));

        $this->assertNull($dispatcher->match('nonexistent'));
        $this->assertEquals(new Match('here will be dragons'), $dispatcher->match('abcd'));


        $this->assertEquals(new Match('dynamic dispatched'), $dispatcher->match('ab'));
        $this->assertEquals(new Match('static overrides dynamic'), $dispatcher->match('a'));
    }
}
