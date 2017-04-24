<?php declare(strict_types=1);

namespace Pinepain\SimpleRouting\Tests\Solutions;

use PHPUnit\Framework\TestCase;
use Pinepain\SimpleRouting\Match;
use Pinepain\SimpleRouting\Route;
use Pinepain\SimpleRouting\Solutions\AdvancedRouter;
use Pinepain\SimpleRouting\Solutions\SimpleRouter;

class AdvancedRouterTest extends TestCase
{
    public function testRemoveTrailingSlash()
    {
        /** @var SimpleRouter | \PHPUnit_Framework_MockObject_MockObject $simple_router */
        $simple_router = $this->getMockBuilder(SimpleRouter::class)
                              ->setMethods(['add', 'match', 'url'])
                              ->disableOriginalConstructor()
                              ->getMock();


        $simple_router->expects($this->exactly(1))
                      ->method('add')
                      ->with('/test', 'handler');

        $simple_router->expects($this->exactly(1))
                      ->method('match')
                      ->with('/test');

        $simple_router->expects($this->exactly(1))
                      ->method('url')
                      ->willReturn('/test/');

        $router = new AdvancedRouter($simple_router, AdvancedRouter::REMOVE_TRAILING_SLASH);

        $router->add('/test/', 'handler');
        $router->match('/test/');

        $this->assertEquals('/test', $router->url('any'));
    }

    public function testEnforceTrailingSlash()
    {
        /** @var SimpleRouter | \PHPUnit_Framework_MockObject_MockObject $simple_router */
        $simple_router = $this->getMockBuilder(SimpleRouter::class)
                              ->setMethods(['add', 'match', 'url'])
                              ->disableOriginalConstructor()
                              ->getMock();


        $simple_router->expects($this->exactly(1))
                      ->method('add')
                      ->with('/test/', 'handler');

        $simple_router->expects($this->exactly(1))
                      ->method('match')
                      ->with('/test/');

        $simple_router->expects($this->exactly(1))
                      ->method('url')
                      ->willReturn('/test');

        $router = new AdvancedRouter($simple_router, AdvancedRouter::ENFORCE_TRAILING_SLASH);

        $router->add('/test', 'handler');
        $router->match('/test');

        $this->assertEquals('/test/', $router->url('any'));
    }

    public function testNoTrailingSlashPolicy()
    {
        /** @var SimpleRouter | \PHPUnit_Framework_MockObject_MockObject $simple_router */
        $simple_router = $this->getMockBuilder(SimpleRouter::class)
                              ->setMethods(['add', 'match', 'url'])
                              ->disableOriginalConstructor()
                              ->getMock();


        $simple_router->expects($this->exactly(2))
                      ->method('add')
                      ->withConsecutive(
                          ['/test/with/trailing/', 'handler-trailing'],
                          ['/test/without/trailing', 'handler-without']
                          );

        $simple_router->expects($this->exactly(2))
                      ->method('match')
                      ->withConsecutive(['/test/with/trailing/'], ['/test/without/trailing'])
                      ->willReturn(new Match('stub', []));

        $simple_router->expects($this->exactly(2))
                      ->method('url')
                      ->withConsecutive(['handler-trailing'],  ['handler-without'])
                      ->willReturnOnConsecutiveCalls('/test/with/trailing/', '/test/without/trailing');

        $router = new AdvancedRouter($simple_router, AdvancedRouter::NO_TRAILING_SLASH_POLICY);

        $router->add('/test/with/trailing/', 'handler-trailing');
        $router->add('/test/without/trailing', 'handler-without');

        $router->match('/test/with/trailing/');
        $router->match('/test/without/trailing');

        $this->assertEquals('/test/with/trailing/', $router->url('handler-trailing'));
        $this->assertEquals('/test/without/trailing', $router->url('handler-without'));
    }
}
