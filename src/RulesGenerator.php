<?php declare(strict_types=1);


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
    private $filter;

    /**
     * @var Compiler
     */
    private $compiler;

    /**
     * @param CompilerFilterInterface $filter
     * @param Compiler                $compiler
     */
    public function __construct(CompilerFilterInterface $filter, Compiler $compiler)
    {
        $this->filter   = $filter;
        $this->compiler = $compiler;
    }

    /**
     * @return int
     */
    public function getApproxChunkSize(): int
    {
        return 10;
    }

    /**
     * @param int $size
     *
     * @return int
     */
    public function calcChunkSize(int $size): int
    {
        $num = max(1, round($size / $this->getApproxChunkSize()));

        return (int)ceil($size / $num);
    }

    /**
     * @param Route[] $routes
     *
     * @return Chunk[]
     */
    public function generate(array $routes)
    {
        $chunks_number = $this->calcChunkSize(count($routes));
        $chunks_routes = $chunks_number ? array_chunk($routes, $chunks_number, true) : [];

        /** @var Chunk[] $chunks */
        $chunks = array_map([$this, 'generateChunk'], $chunks_routes);

        return $chunks;
    }

    /**
     * @param Route[] $routes
     *
     * @return Chunk
     */
    public function generateChunk(array $routes): Chunk
    {
        $num_groups = 1;
        $regexes    = [];

        $routes_map = [];

        foreach ($routes as $rule => $route) {
            $chunks    = $this->filter->filter($route->chunks);
            $compiled  = $this->compiler->compile($chunks);
            $variables = $compiled->getVariables();

            $num_variables = count($variables);
            $num_groups    = max($num_groups, $num_variables);

            if ($compiled->hasOptional() && $num_variables >= $num_groups) {
                $num_groups++;
            }

            $regexes[] = $compiled->getRegex() . str_repeat('()', $num_groups - $num_variables);

            $routes_map[$num_groups + 1] = new Crumb($route->handler, $variables); // +1 because of 0-match (whole given string)

            $num_groups++;
        }

        $regex = static::REGEX_DELIMITER . '^(?|' . implode('|', $regexes) . ')$' . static::REGEX_DELIMITER;

        return new Chunk($regex, $routes_map);
    }
}
