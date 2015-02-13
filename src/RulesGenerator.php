<?php


namespace Pinepain\SimpleRouting;


// original idea to join regex rules with match group - by Nikita Popov's FastRoute https://github.com/nikic/FastRoute
// blog post - http://nikic.github.io/2014/02/18/Fast-request-routing-using-regular-expressions.html
class RulesGenerator
{
    const REGEX_DELIMITER = '~';

    /**
     * @var Compiler
     */
    private $compiler;

    /**
     * @var Filter
     */
    private $filter;

    /**
     * @param Filter   $filter
     * @param Compiler $compiler
     */
    public function __construct($filter, $compiler)
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

    public function generateChunk(array $routes)
    {
        $num_groups = 1;
        $regexes    = [];

        $routes_map = [];

        foreach ($routes as $route => $handler_and_parsed) {
            list ($handler, $parsed) = $handler_and_parsed;

            $parsed   = $this->filter->filter($parsed);
            $compiled = $this->compiler->compile($parsed);

            /** @var $compiled CompiledRoute */
            $variables = $compiled->getVariables();

            $num_variables = count($variables);
            $num_groups    = max($num_groups, $num_variables);

            if ($compiled->hasOptional() && $num_variables >= $num_groups) {
                $num_groups++;
            }

            $regexes[] = $compiled->getRegex() . str_repeat('()', $num_groups - $num_variables);

            $routes_map[$num_groups + 1] = [$handler, $variables]; // +1 because of 0-match (whole given string)

            $num_groups++;
        }

        $regex = static::REGEX_DELIMITER . '^(?|' . implode('|', $regexes) . ')$' . static::REGEX_DELIMITER;

        return [$regex, $routes_map];

    }
}