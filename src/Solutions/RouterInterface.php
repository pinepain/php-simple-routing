<?php declare(strict_types=1);

namespace Pinepain\SimpleRouting\Solutions;

use Pinepain\SimpleRouting\Exception;
use Pinepain\SimpleRouting\Match;
use Pinepain\SimpleRouting\Route;

interface RouterInterface
{
    /**
     * @param string $route
     * @param string $handler
     *
     * @return Route
     *
     * @throws Exception
     */
    public function add(string $route, string $handler): Route;

    /**
     * @param string $url
     *
     * @return Match
     */
    public function match(string $url): Match;

    /**
     * Generate URL
     *
     * @param string $handler   Route definition identifier
     * @param array  $arguments Route parameter values
     * @param bool   $full      Whether missed optional parameters should be included in built URL
     *
     * @throws Exception When handler route mapping does not exist
     * @return string Generated URL
     */
    public function url(string $handler, array $arguments = [], bool $full = false): string;
}
