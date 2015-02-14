<?php


namespace Pinepain\SimpleRouting\Solutions;

use Pinepain\SimpleRouting\Dispatcher;
use Pinepain\SimpleRouting\Filter;
use Pinepain\SimpleRouting\RoutesCollector;
use Pinepain\SimpleRouting\RulesGenerator;
use Pinepain\SimpleRouting\UrlGenerator;


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
    /**
     * @var UrlGenerator
     */
    private $url_generator;

    public function __construct(RoutesCollector $collector, RulesGenerator $generator, Dispatcher $dispatcher, UrlGenerator $url_generator)
    {
        $this->collector     = $collector;
        $this->generator     = $generator;
        $this->dispatcher    = $dispatcher;
        $this->url_generator = $url_generator;
    }

    public function add($route, $handler)
    {
        return $this->collector->add($route, $handler);
    }

    public function dispatch($url)
    {
        $dynamic_routes = $this->collector->getDynamicRoutes();
        $static_routes  = $this->collector->getStaticRoutes();

        $dynamic_rules = $this->generator->generate($dynamic_routes);

        $this->dispatcher->setStaticRules($static_routes);
        $this->dispatcher->setDynamicRules($dynamic_rules);

        return $this->dispatcher->dispatch($url);
    }

    public function url($handler, array $arguments = array(), $full = false)
    {
        $dynamic_routes = $this->collector->getDynamicRoutes();

        $this->url_generator->setMapFromRoutes($dynamic_routes);

        $url = $this->url_generator->generate($handler, $arguments, $full);

        return $url;
    }
}