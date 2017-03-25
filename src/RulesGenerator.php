<?php


namespace Pinepain\SimpleRouting;


// original idea to join regex rules with match group - by Nikita Popov's FastRoute https://github.com/nikic/FastRoute
// blog post - http://nikic.github.io/2014/02/18/Fast-request-routing-using-regular-expressions.html
use Pinepain\SimpleRouting\Contracts\CompilerFilterInterface;

class RulesGenerator
{
    const REGEX_DELIMITER = '~';

    /**
     * @var CompilerFilterInterface
     */
    private $compiler;

    /**
     * @var Filter
     */
    private $filter;

    /**
     * @param CompilerFilterInterface $filter
     * @param Compiler $compiler
     */
    public function __construct(CompilerFilterInterface $filter, Compiler $compiler)
    {
        $this->filter   = $filter;
        $this->compiler = $compiler;
    }

    public function getApproxChunkSize()
    {
        return 10;
    }

    public function calcChunkSize($size)
    {
        $num = max(1, round($size / $this->getApproxChunkSize()));

        return ceil($size / $num);
    }


    public function generate(array $routes)
    {
        $chunks_number = $this->calcChunkSize(count($routes));
        $chunks_routes = $chunks_number ? array_chunk($routes, $chunks_number, true) : [];

        $chunks = array_map([$this, 'generateChunk'], $chunks_routes);

        return $chunks;
    }

    /**
     * @param Route[] $routes
     *
     * @return array
     */
    public function generateChunk(array $routes)
    {
        $num_groups = 1;
        $regexes    = [];

        $routes_map = [];

        foreach ($routes as $rule => $route) {

            $chunks   = $this->filter->filter($route->chunks);
            $compiled = $this->compiler->compile($chunks);

            /** @var $compiled CompiledRoute */
            $variables = $compiled->getVariables();

            $num_variables = count($variables);
            $num_groups    = max($num_groups, $num_variables);

            if ($compiled->hasOptional() && $num_variables >= $num_groups) {
                $num_groups++;
            }

            $regexes[] = $compiled->getRegex() . str_repeat('()', $num_groups - $num_variables);

            $routes_map[$num_groups + 1] = [$route->handler, $variables]; // +1 because of 0-match (whole given string)

            $num_groups++;
        }

        $regex = static::REGEX_DELIMITER . '^(?|' . implode('|', $regexes) . ')$' . static::REGEX_DELIMITER;

        return [$regex, $routes_map];

    }
}
