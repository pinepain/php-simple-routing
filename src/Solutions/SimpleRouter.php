<?php


namespace Pinepain\SimpleRouting\Solutions;

use Pinepain\SimpleRouting\Dispatcher;
use Pinepain\SimpleRouting\Filter;
use Pinepain\SimpleRouting\RoutesCollector;
use Pinepain\SimpleRouting\RulesGenerator;


class SimpleRouter
{
    /**
     * @var RoutesCollector
     */
    private $collector;
    /**
     * @var RulesGenerator
     */
    private $generator;
    /**
     * @var Dispatcher
     */
    private $dispatcher;

    public function __construct(RoutesCollector $collector, RulesGenerator $generator, Dispatcher $dispatcher)
    {
        $this->collector  = $collector;
        $this->generator  = $generator;
        $this->dispatcher = $dispatcher;
    }

    public function add($route, $handler)
    {
        return $this->collector->add($route, $handler);
    }

    public function dispatch($url)
    {
        $dynamic_routes = $this->collector->getDynamicRoutes();

        $static_rules  = $this->collector->getStaticRoutes();

        $dynamic_rules = $this->generator->generate($dynamic_routes);

        $this->dispatcher->setStaticRules($static_rules);
        $this->dispatcher->setDynamicRules($dynamic_rules);

        return $this->dispatcher->dispatch($url);
    }
}