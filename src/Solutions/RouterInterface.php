<?php

namespace Pinepain\SimpleRouting\Solutions;

use Pinepain\SimpleRouting\Exception;
use Pinepain\SimpleRouting\Match;
use Pinepain\SimpleRouting\Route;

interface RouterInterface
{
    /**
     * @param string $route
     * @param $handler
     *
     * @return Route
     *
     * @throws Exception
     */
    public function add($route, $handler);

    /**
     * @param string $url
     *
     * @return Match|null
     */
    public function match($url);

    /**
     * Generate URL
     *
     * @param string $handler Route definition identifier
     * @param array $arguments Route parameter values
     * @param bool $full Whether missed optional parameters should be included in built URL
     *
     * @return string Generated URL
     */
    public function url($handler, array $arguments = [], $full = false);
}
