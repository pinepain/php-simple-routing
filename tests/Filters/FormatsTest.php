<?php


namespace Pinepain\SimpleRouting\Tests\Filters;


use Pinepain\SimpleRouting\Filters\Formats;

class FormatsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers \Pinepain\SimpleRouting\Filters\Formats::handleMissedFormat
     */
    public function testHandleMissedFormat()
    {
        $collection = $this->getMock('Pinepain\SimpleRouting\Filters\Helpers\FormatsCollection');

        $filter = new Formats($collection);

        $this->assertEquals('format-1', $filter->handleMissedFormat('name-1', 'format-1'));
        $this->assertEquals('format-2', $filter->handleMissedFormat('name-2', 'format-2'));
    }

    /**
     * @covers \Pinepain\SimpleRouting\Filters\Formats::__construct
     * @covers \Pinepain\SimpleRouting\Filters\Formats::filter
     */
    public function testFilter()
    {
        $collection = $this->getMock('Pinepain\SimpleRouting\Filters\Helpers\FormatsCollection', ['find']);


        $collection->expects($this->at(0))
            ->method('find')
            ->with('found')
            ->willReturn('found-regex');

        $collection->expects($this->at(1))
            ->method('find')
            ->with('default-x')
            ->willReturn('default-regex');

        $collection->expects($this->at(2))
            ->method('find')
            ->with('missed')
            ->willReturn(null);


        $filter = $this->getMock(
            '\Pinepain\SimpleRouting\Filters\Formats',
            ['handleMissedFormat'],
            [$collection, 'default-x']
        );

        $filter->expects($this->once())
            ->method('handleMissedFormat')
            ->willReturn('missed-handled');

        $parsed = [
            'static',
            ['test-found', 'found', 'default', 'delimiter'],
            ['test-default', null, 'default', 'delimiter'],
            ['test-missed', 'missed', 'default', 'delimiter'],
        ];

        $filtered = [
            'static',
            ['test-found', 'found-regex', 'default', 'delimiter'],
            ['test-default', 'default-regex', 'default', 'delimiter'],
            ['test-missed', 'missed-handled', 'default', 'delimiter'],
        ];

        $this->assertEquals($filtered, $filter->filter($parsed));

    }
}
