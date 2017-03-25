<?php

namespace Pinepain\SimpleRouting\Tests\Filters;

use PHPUnit\Framework\TestCase;
use Pinepain\SimpleRouting\Chunks\DynamicChunk;
use Pinepain\SimpleRouting\Chunks\StaticChunk;
use Pinepain\SimpleRouting\CompilerFilters\Formats;
use Pinepain\SimpleRouting\CompilerFilters\Helpers\FormatsCollection;

class FormatsTest extends TestCase
{
    /**
     * @covers \Pinepain\SimpleRouting\CompilerFilters\Formats::handleMissedFormat
     */
    public function testHandleMissedFormat()
    {
        /** @var FormatsCollection | \PHPUnit_Framework_MockObject_MockObject $collection */
        $collection = $this->getMockBuilder(FormatsCollection::class)->getMock();

        $filter = new Formats($collection);

        $this->assertEquals('format-1', $filter->handleMissedFormat('name-1', 'format-1'));
        $this->assertEquals('format-2', $filter->handleMissedFormat('name-2', 'format-2'));
    }

    /**
     * @covers \Pinepain\SimpleRouting\CompilerFilters\Formats::__construct
     * @covers \Pinepain\SimpleRouting\CompilerFilters\Formats::filter
     */
    public function testFilter()
    {
        $collection = $this->getMockBuilder(FormatsCollection::class, ['find'])->getMock();

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


        /** @var Formats | \PHPUnit_Framework_MockObject_MockObject $filter */
        $filter = $this->getMockBuilder(Formats::class)
                       ->setMethods(['handleMissedFormat'])
                       ->setConstructorArgs([$collection, 'default-x'])
                       ->getMock();

        $filter->expects($this->once())
               ->method('handleMissedFormat')
               ->willReturn('missed-handled');

        $parsed = [
            new StaticChunk('static'),
            new DynamicChunk('test-found', 'found', 'default', 'delimiter'),
            new DynamicChunk('test-default', null, 'default', 'delimiter'),
            new DynamicChunk('test-missed', 'missed', 'default', 'delimiter'),
        ];

        $filtered = [
            new StaticChunk('static'),
            new DynamicChunk('test-found', 'found-regex', 'default', 'delimiter'),
            new DynamicChunk('test-default', 'default-regex', 'default', 'delimiter'),
            new DynamicChunk('test-missed', 'missed-handled', 'default', 'delimiter'),
        ];

        $this->assertEquals($filtered, $filter->filter($parsed));

    }
}
