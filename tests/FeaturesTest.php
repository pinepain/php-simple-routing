<?php declare(strict_types=1);

namespace Pinepain\SimpleRouting\Tests;

use PHPUnit\Framework\TestCase;
use Pinepain\SimpleRouting\Compiler;
use Pinepain\SimpleRouting\CompilerFilters\Formats;
use Pinepain\SimpleRouting\CompilerFilters\Helpers\FormatsCollection;
use Pinepain\SimpleRouting\Filter;
use Pinepain\SimpleRouting\FormatHandlers\Path as PathFormatHandler;
use Pinepain\SimpleRouting\FormatsHandler;
use Pinepain\SimpleRouting\Match;
use Pinepain\SimpleRouting\Matcher;
use Pinepain\SimpleRouting\Parser;
use Pinepain\SimpleRouting\RoutesCollector;
use Pinepain\SimpleRouting\RulesGenerator;
use Pinepain\SimpleRouting\Solutions\SimpleRouter;
use Pinepain\SimpleRouting\UrlGenerator;

class FeaturesTest extends TestCase
{
    /**
     * @var SimpleRouter
     */
    private $router;

    protected function setUp()
    {
        parent::setUp();

        $formats_preset = [
            ['segment', '[^/]+', ['default']],
            ['alpha', '[[:alpha:]]+', ['a']],
            ['digit', '[[:digit:]]+', ['d']],
            ['word', '[\w]+', ['w']],
            // see http://stackoverflow.com/questions/19256323/regex-to-match-a-slug
            ['slug', '[a-z0-9]+(?:-[a-z0-9]+)*', ['s']],
            ['path', '.+', ['p']],
        ];

        $collector       = new RoutesCollector(new Parser());
        $filter          = new Filter([new Formats(new FormatsCollection($formats_preset))]);
        $generator       = new RulesGenerator($filter, new Compiler());
        $dispatcher      = new Matcher();
        $formats_handler = new FormatsHandler([new PathFormatHandler()]);
        $url_generator   = new UrlGenerator($formats_handler);

        $this->router = new SimpleRouter($collector, $generator, $dispatcher, $url_generator);

    }

    public function testDashesInRoute()
    {
        $router = $this->router;

        $router->add('/route/with-dash/{some-param}', 'dash');
        $router->add('/static/route/with-dash', 'static.dash');

        $res = $router->match('/route/with-dash/some-value');

        $this->assertInstanceOf(Match::class, $res);
        $this->assertSame('dash', $res->handler);
        $this->assertSame(['some-param' => 'some-value'], $res->variables);

        $this->assertSame('/route/with-dash/some-value', $router->url('dash', ['some-param' => 'some-value']));

        $res = $router->match('/static/route/with-dash');

        $this->assertInstanceOf(Match::class, $res);
        $this->assertSame('static.dash', $res->handler);
        $this->assertSame([], $res->variables);
    }

    public function testTrailingSlash()
    {
        $router = $this->router;

        $router->add('/with/trailing/slash/', 'with.trailing');
        $router->add('/without/trailing/slash', 'without.trailing');
        $router->add('/without/trailing/slash/', 'without.trailing.second');

        $res = $router->match('/with/trailing/slash/');

        $this->assertInstanceOf(Match::class, $res);
        $this->assertSame('with.trailing', $res->handler);


        $res = $router->match('/without/trailing/slash');
        $this->assertInstanceOf(Match::class, $res);
        $this->assertSame('without.trailing', $res->handler);

        $res = $router->match('/without/trailing/slash/');
        $this->assertInstanceOf(Match::class, $res);
        $this->assertSame('without.trailing.second', $res->handler);
    }

    /**
     * @expectedException \Pinepain\SimpleRouting\NotFoundException
     * @expectedExceptionMessage Url '/nonexistent' does not match any route
     */
    public function testNonexistent() {
        $router = $this->router;
        $router->match('/nonexistent');
    }

    public function testGenerateUrls() {
        $router = $this->router;

        $router->add('/static/url', 'static');
        $router->add('/dynamic/{prop}', 'dynamic');

        $this->assertSame('/static/url', $router->url('static'));
        $this->assertSame('/dynamic/value', $router->url('dynamic', ['prop' => 'value']));
    }
}
