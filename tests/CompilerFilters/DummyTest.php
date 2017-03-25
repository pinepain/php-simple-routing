<?php

namespace Pinepain\SimpleRouting\Tests\Filters;

use Pinepain\SimpleRouting\CompilerFilters\Dummy;

class DummyTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers \Pinepain\SimpleRouting\CompilerFilters\Dummy
     */
    public function testHandleMissedFormat()
    {
        $filter = new Dummy();

        $this->assertEquals([], $filter->filter([]));
        $this->assertEquals(['test'], $filter->filter(['test']));
    }
}
