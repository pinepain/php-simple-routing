<?php

namespace Pinepain\SimpleRouting\Tests\Solutions;

use PHPUnit\Framework\TestCase;
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

}
