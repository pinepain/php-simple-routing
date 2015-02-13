<?php


namespace Pinepain\SimpleRouting\Tests;

use Mockery as m;

use Pinepain\SimpleRouting\Filter;

class FilterTest extends \PHPUnit_Framework_TestCase
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

        $filter_1 = m::mock('stdClass');
        $filter_1->shouldReceive('filter')->with(['parsed', 'route'])->andReturn(['parsed', 'route', 'filtered']);

        $filter_2 = m::mock('stdClass');
        $filter_2->shouldReceive('filter')->with(['parsed', 'route', 'filtered'])->andReturn([
            'parsed',
            'route',
            'filtered',
            'twice'
        ]);

        $filter->setFilters([$filter_1, $filter_2]);

        $this->assertSame(['parsed', 'route', 'filtered', 'twice'], $filter->filter(['parsed', 'route']));
    }

    protected function tearDown()
    {
        parent::tearDown();
        m::close();
    }


}
