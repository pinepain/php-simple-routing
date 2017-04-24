<?php declare(strict_types=1);

namespace Pinepain\SimpleRouting\Tests;

use PHPUnit\Framework\TestCase;
use Pinepain\SimpleRouting\Contracts\CompilerFilterInterface;
use Pinepain\SimpleRouting\Filter;

class FilterTest extends TestCase
{
    /**
     * @var Filter
     */
    private $filter;

    protected function setUp()
    {
        parent::setUp();

        $this->filter = new Filter();
    }

    /**
     * @covers \Pinepain\SimpleRouting\Filter::getFilters
     * @covers \Pinepain\SimpleRouting\Filter::setFilters
     */
    public function testGetSetFilter()
    {
        $filter = $this->filter;

        $filters = ['here will be dragons'];

        $this->assertSame([], $filter->getFilters());
        $filter->setFilters($filters);
        $this->assertSame($filters, $filter->getFilters());
    }

    /**
     * @covers \Pinepain\SimpleRouting\Filter::__construct
     * @covers \Pinepain\SimpleRouting\Filter::getFilters
     */
    public function testConstructor()
    {
        $filters = ['here will be dragons'];

        $filter = new Filter();
        $this->assertSame([], $filter->getFilters());

        $filter = new Filter($filters);
        $this->assertSame($filters, $filter->getFilters());
    }

    /**
     * @covers \Pinepain\SimpleRouting\Filter::filter
     */
    public function testFilter()
    {
        $filter = $this->filter;

        /** @var CompilerFilterInterface | \PHPUnit_Framework_MockObject_MockObject $filter_1 */
        $filter_1 = $this->getMockBuilder(CompilerFilterInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['filter'])
            ->getMock();

        /** @var CompilerFilterInterface | \PHPUnit_Framework_MockObject_MockObject $filter_2 */
        $filter_2 = $this->getMockBuilder(CompilerFilterInterface::class)
                         ->disableOriginalConstructor()
                         ->setMethods(['filter'])
                         ->getMock();

        $filter_1->expects($this->once())
            ->method('filter')
            ->with(['parsed', 'route'])
            ->willReturn(['parsed', 'route', 'filtered']);

        $filter_2->expects($this->once())
                 ->method('filter')
                 ->with(['parsed', 'route', 'filtered'])
                 ->willReturn(['parsed', 'route', 'filtered', 'twice']);

        $filter->setFilters([$filter_1, $filter_2]);

        $this->assertSame(['parsed', 'route', 'filtered', 'twice'], $filter->filter(['parsed', 'route']));
    }
}
