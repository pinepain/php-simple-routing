<?php


namespace Pinepain\SimpleRouting\Tests;


use Pinepain\SimpleRouting\Matcher;

class MatcherTest extends \PHPUnit_Framework_TestCase
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
                'second-optional' => 'second-default'
            ],
            $dispatcher->extractVariablesFromMatches(
                ['whole-match', 'first-val', 'second-val', 'first-optional-val'],
                [
                    'first'           => 'bool false',
                    'second'          => 'bool false',
                    'first-optional'  => 'first-default',
                    'second-optional' => 'second-default'
                ]
            )
        );
    }

    /**
     * @covers \Pinepain\SimpleRouting\Matcher::matchDynamicRoute
     */
    public function testMatchDynamicRoute()
    {
        /** @var \Pinepain\SimpleRouting\Matcher | \PHPUnit_Framework_MockObject_MockObject $dispatcher */
        $dispatcher = $this->getMock('Pinepain\SimpleRouting\Matcher', ['extractVariablesFromMatches']);

        $dispatcher->expects($this->atLeast(1))->method('extractVariablesFromMatches')->willReturn('resolved-vars');

        $dynamic_rules = [
            [
                '~^(?|(a)|(a)(b)|(a)(b)(c))$~',
                [
                    2 => ['handler-1', ['first vars']],
                    3 => ['handler-2', ['second vars']],
                    4 => ['handler-3', ['third vars']],
                ]
            ]
        ];

        $this->assertNull($dispatcher->matchDynamicRoute([], 'ab'));
        $this->assertNull($dispatcher->matchDynamicRoute([['~never-matches~', []]], 'ab'));

        $this->assertEquals(['handler-2', 'resolved-vars'], $dispatcher->matchDynamicRoute($dynamic_rules, 'ab'));
    }

    /**
     * @covers \Pinepain\SimpleRouting\Matcher::__construct
     * @covers \Pinepain\SimpleRouting\Matcher::match
     * @covers \Pinepain\SimpleRouting\Matcher::setStaticRules
     * @covers \Pinepain\SimpleRouting\Matcher::setDynamicRules
     */
    public function testMatch()
    {
        $static_rules  = ['abcd' => 'here will be dragons', 'a' => 'static overrides dynamic'];
        $dynamic_rules = ['here will be no dragons, but only dynamic rules'];

        /** @var \Pinepain\SimpleRouting\Matcher | \PHPUnit_Framework_MockObject_MockObject $dispatcher */
        $dispatcher = $this->getMock(
            'Pinepain\SimpleRouting\Matcher',
            ['matchDynamicRoute'],
            [$static_rules, $dynamic_rules]
        );

        $dispatcher->expects($this->at(0))
            ->method('matchDynamicRoute')
            ->with($dynamic_rules, 'nonexistent')
            ->willReturn(null);

        $dispatcher->expects($this->at(1))
            ->method('matchDynamicRoute')
            ->with($dynamic_rules, 'ab')
            ->willReturn('dynamic dispatched');

        $this->assertNull($dispatcher->match('nonexistent'));
        $this->assertEquals('here will be dragons', $dispatcher->match('abcd'));


        $this->assertEquals('dynamic dispatched', $dispatcher->match('ab'));
        $this->assertEquals('static overrides dynamic', $dispatcher->match('a'));
    }
}
