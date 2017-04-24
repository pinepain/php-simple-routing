<?php declare(strict_types=1);


namespace Pinepain\SimpleRouting\Tests\Filters;

use PHPUnit\Framework\TestCase;
use Pinepain\SimpleRouting\CompilerFilters\Helpers\FormatsCollection;

class FormatsCollectionTest extends TestCase
{
    private $preset = [
        ['segment', '[^/]+', ['default']],
        ['alpha', '[[:alpha:]]+', ['a']],
        ['digit', '[[:digit:]]+', ['d']],
        ['word', '[\w]+', ['w']],
        // see http://stackoverflow.com/questions/19256323/regex-to-match-a-slug
        ['slug', '[a-z0-9]+(?:-[a-z0-9]+)*', ['s']],
        ['path', '.+', 'p'],
    ];

    /**
     * @covers \Pinepain\SimpleRouting\CompilerFilters\Helpers\FormatsCollection::__construct
     * @covers \Pinepain\SimpleRouting\CompilerFilters\Helpers\FormatsCollection::add
     * @covers \Pinepain\SimpleRouting\CompilerFilters\Helpers\FormatsCollection::find
     */
    public function testFind()
    {
        $collection = new FormatsCollection($this->preset);

        $this->assertNull($collection->find('nonexistent'));
        $this->assertSame('[^/]+', $collection->find('segment'));
        $this->assertSame('[^/]+', $collection->find('default'));
    }

    /**
     * @covers \Pinepain\SimpleRouting\CompilerFilters\Helpers\FormatsCollection::add
     * @covers \Pinepain\SimpleRouting\CompilerFilters\Helpers\FormatsCollection::find
     */
    public function testAdd()
    {
        $collection = new FormatsCollection($this->preset);

        $collection->add('test', 'regex', ['test-alias-1', 'test-alias-2']);

        $this->assertNull($collection->find('nonexistent'));
        $this->assertSame('regex', $collection->find('test'));
        $this->assertSame('regex', $collection->find('test-alias-1'));
        $this->assertSame('regex', $collection->find('test-alias-2'));
    }

    /**
     * @covers \Pinepain\SimpleRouting\CompilerFilters\Helpers\FormatsCollection::remove
     * @covers \Pinepain\SimpleRouting\CompilerFilters\Helpers\FormatsCollection::find
     */
    public function testRemove()
    {
        $collection = new FormatsCollection($this->preset);

        $collection->remove('segment');

        $this->assertNull($collection->find('nonexistent'));
        $this->assertNull($collection->find('segment'));
        $this->assertNull($collection->find('default'));
    }
}
