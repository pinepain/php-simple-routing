<?php declare(strict_types=1);


namespace Pinepain\SimpleRouting\Solutions;

use Pinepain\SimpleRouting\Match;
use Pinepain\SimpleRouting\Matcher;
use Pinepain\SimpleRouting\Route;
use Pinepain\SimpleRouting\RoutesCollector;
use Pinepain\SimpleRouting\RulesGenerator;
use Pinepain\SimpleRouting\UrlGenerator;


class SimpleRouter implements RouterInterface
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
     * @var Matcher
     */
    private $matcher;
    /**
     * @var UrlGenerator
     */
    private $url_generator;

    public function __construct(RoutesCollector $collector, RulesGenerator $generator, Matcher $matcher, UrlGenerator $url_generator)
    {
        $this->collector     = $collector;
        $this->generator     = $generator;
        $this->matcher       = $matcher;
        $this->url_generator = $url_generator;
    }

    /**
     * {@inheritdoc}
     */
    public function add(string $route, string $handler): Route
    {
        return $this->collector->add($route, $handler);
    }

    /**
     * {@inheritdoc}
     */
    public function match(string $url): Match
    {
        $dynamic_routes = $this->collector->getDynamicRoutes();
        $static_routes  = $this->collector->getStaticRoutes();

        $dynamic_rules = $this->generator->generate($dynamic_routes);

        $this->matcher->setStaticRules($static_routes);
        $this->matcher->setDynamicRules($dynamic_rules);

        return $this->matcher->match($url);
    }

    /**
     * {@inheritdoc}
     */
    public function url(string $handler, array $arguments = [], bool $full = false): string
    {
        $this->url_generator->setMapFromRoutesCollector($this->collector);

        $url = $this->url_generator->generate($handler, $arguments, $full);

        return $url;
    }
}
