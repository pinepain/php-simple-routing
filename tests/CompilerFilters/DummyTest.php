<?php declare(strict_types=1);

namespace Pinepain\SimpleRouting\Tests\Filters;

use PHPUnit\Framework\TestCase;
use Pinepain\SimpleRouting\CompilerFilters\Dummy;

class DummyTest extends TestCase
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
