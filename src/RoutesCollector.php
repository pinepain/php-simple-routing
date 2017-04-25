<?php declare(strict_types=1);


namespace Pinepain\SimpleRouting;


use Pinepain\SimpleRouting\Chunks\AbstractChunk;

class RoutesCollector
{
    /**
     * @var Parser
     */
    private $parser;

    /**
     * @var Route[]
     */
    private $static = [];

    /**
     * @var Route[]
     */
    private $dynamic = [];

    /**
     * @param Parser $parser
     */
    public function __construct(Parser $parser)
    {
        $this->parser = $parser;
    }

    /**
     * @param string $route
     * @param string $handler
     *
     * @return Route
     * @throws Exception
     */
    public function add(string $route, string $handler)
    {
        if (isset($this->static[$route]) || isset($this->dynamic[$route])) {
            throw new Exception("Route '{$route}' already registered");
        }

        $parsed = $this->parser->parse($route);

        $r = new Route($handler, $parsed);

        if ($this->isStatic($parsed)) {
            $this->static[$route] = $r;
        } else {
            $this->dynamic[$route] = $r;
        }

        return $r;
    }

    /**
     * @param AbstractChunk[] $parsed
     *
     * @return bool
     */
    public function isStatic(array $parsed)
    {
        return count($parsed) == 1 && $parsed[0]->isStatic();
    }

    /**
     * @return Route[]
     */
    public function getStaticRoutes()
    {
        return $this->static;
    }

    /**
     * @return Route[]
     */
    public function getDynamicRoutes()
    {
        return $this->dynamic;
    }
}
