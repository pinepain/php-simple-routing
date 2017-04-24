<?php declare(strict_types=1);

namespace Pinepain\SimpleRouting\Tests;

use PHPUnit\Framework\TestCase;
use Pinepain\SimpleRouting\CompiledRoute;

class CompiledRouteTest extends TestCase
{
    /**
     * @covers \Pinepain\SimpleRouting\CompiledRoute::__construct
     * @covers \Pinepain\SimpleRouting\CompiledRoute::getRegex
     * @covers \Pinepain\SimpleRouting\CompiledRoute::getVariables
     * @covers \Pinepain\SimpleRouting\CompiledRoute::hasOptional
     */
    public function testConstructorAndGetters()
    {
        $route = new CompiledRoute('regex', ['variable' => 'default'], true);

        $this->assertEquals('regex', $route->getRegex());
        $this->assertEquals(['variable' => 'default'], $route->getVariables());
        $this->assertEquals(true, $route->hasOptional());
    }
}
