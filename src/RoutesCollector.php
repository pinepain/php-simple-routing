<?php


namespace Pinepain\SimpleRouting;


class RoutesCollector
{
    private $parser;

    private $static = [];
    private $dynamic = [];

    /**
     * @param Parser $parser
     */
    public function __construct(Parser $parser)
    {
        $this->parser = $parser;
    }

    public function add($route, $handler)
    {
        if (isset($this->static[$route]) || isset($this->dynamic[$route])) {
            throw new Exception("Route '{$route}' already registered");
        }

        $parsed = $this->parser->parse($route);

        if ($this->isStatic($parsed)) {
            $this->static[$route] = [$handler, $parsed];
        } else {
            $this->dynamic[$route] = [$handler, $parsed];
        }

        return $parsed;
    }

    public function isStatic(array $parsed)
    {
        return count($parsed) == 1 && is_string($parsed[0]);
    }

    public function getStaticRoutes()
    {
        return $this->static;
    }

    public function getDynamicRoutes()
    {
        return $this->dynamic;
    }
}
