<?php


namespace Pinepain\SimpleRouting\Tests\Filters;


use Pinepain\SimpleRouting\Filters\Helpers\FormatsCollection;

class FormatsCollectionTest extends \PHPUnit_Framework_TestCase
{
    private $preset = [
        ['segment', '[^/]+', ['default']],
        ['alpha', '[[:alpha:]]+', ['a']],
        ['digit', '[[:digit:]]+', ['d']],
        ['word', '[\w]+', ['w']],
        // see http://stackoverflow.com/questions/19256323/regex-to-match-a-slug
        ['slug', '[a-z0-9]+(?:-[a-z0-9]+)*', ['s']],
    ];

    /**
     * @covers \Pinepain\SimpleRouting\Filters\Helpers\FormatsCollection::__construct
     * @covers \Pinepain\SimpleRouting\Filters\Helpers\FormatsCollection::add
     * @covers \Pinepain\SimpleRouting\Filters\Helpers\FormatsCollection::find
     */
    public function testFind()
    {
        $collection = new FormatsCollection($this->preset);

        $this->assertNull($collection->find('nonexistent'));
        $this->assertSame('[^/]+', $collection->find('segment'));
        $this->assertSame('[^/]+', $collection->find('default'));
    }

    /**
     * @covers \Pinepain\SimpleRouting\Filters\Helpers\FormatsCollection::add
     * @covers \Pinepain\SimpleRouting\Filters\Helpers\FormatsCollection::find
     */
    public function testAdd()
    {
        $collection = new \Pinepain\SimpleRouting\Filters\Helpers\FormatsCollection($this->preset);

        $collection->add('test', 'regex', ['test-alias-1', 'test-alias-2']);

        $this->assertNull($collection->find('nonexistent'));
        $this->assertSame('regex', $collection->find('test'));
        $this->assertSame('regex', $collection->find('test-alias-1'));
        $this->assertSame('regex', $collection->find('test-alias-2'));
    }

    /**
     * @covers \Pinepain\SimpleRouting\Filters\Helpers\FormatsCollection::remove
     * @covers \Pinepain\SimpleRouting\Filters\Helpers\FormatsCollection::find
     */
    public function testRemove()
    {
        $collection = new \Pinepain\SimpleRouting\Filters\Helpers\FormatsCollection($this->preset);

        $collection->remove('segment');

        $this->assertNull($collection->find('nonexistent'));
        $this->assertNull($collection->find('segment'));
        $this->assertNull($collection->find('default'));
    }
}
