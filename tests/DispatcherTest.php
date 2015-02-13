<?php


namespace Pinepain\SimpleRouting\Tests;


use Pinepain\SimpleRouting\Dispatcher;

class DispatcherTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @covers \Pinepain\SimpleRouting\Dispatcher::extractVariablesFromMatches
     */
    public function testExtractVariablesFromMatches()
    {
        $dispatcher = new Dispatcher([], []);


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
     * @covers \Pinepain\SimpleRouting\Dispatcher::dispatchDynamicRoute
     */
    public function testDispatchDynamicRoute()
    {
        /** @var \Pinepain\SimpleRouting\Dispatcher | \PHPUnit_Framework_MockObject_MockObject $dispatcher */
        $dispatcher = $this->getMock('Pinepain\SimpleRouting\Dispatcher', ['extractVariablesFromMatches']);

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

        $this->assertNull($dispatcher->dispatchDynamicRoute([], 'ab'));
        $this->assertNull($dispatcher->dispatchDynamicRoute([['~never-matches~', []]], 'ab'));

        $this->assertEquals(['handler-2', 'resolved-vars'], $dispatcher->dispatchDynamicRoute($dynamic_rules, 'ab'));
    }

    /**
     * @covers \Pinepain\SimpleRouting\Dispatcher::__construct
     * @covers \Pinepain\SimpleRouting\Dispatcher::dispatch
     * @covers \Pinepain\SimpleRouting\Dispatcher::setStaticRules
     * @covers \Pinepain\SimpleRouting\Dispatcher::setDynamicRules
     */
    public function testDispatch()
    {
        $static_rules  = ['abcd' => 'here will be dragons', 'a' => 'static overrides dynamic'];
        $dynamic_rules = ['here will be no dragons, but only dynamic rules'];

        /** @var \Pinepain\SimpleRouting\Dispatcher | \PHPUnit_Framework_MockObject_MockObject $dispatcher */
        $dispatcher = $this->getMock('Pinepain\SimpleRouting\Dispatcher', ['dispatchDynamicRoute'], [$static_rules, $dynamic_rules]);

        $dispatcher->expects($this->at(0))
            ->method('dispatchDynamicRoute')
            ->with($dynamic_rules, 'nonexistent')
            ->willReturn(null);

        $dispatcher->expects($this->at(1))
            ->method('dispatchDynamicRoute')
            ->with($dynamic_rules, 'ab')
            ->willReturn('dynamic dispatched');

        $this->assertNull($dispatcher->dispatch('nonexistent'));
        $this->assertEquals('here will be dragons', $dispatcher->dispatch('abcd'));


        $this->assertEquals('dynamic dispatched', $dispatcher->dispatch('ab'));
        $this->assertEquals('static overrides dynamic', $dispatcher->dispatch('a'));
    }
}
