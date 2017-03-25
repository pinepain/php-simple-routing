<?php


namespace Pinepain\SimpleRouting;


use Pinepain\SimpleRouting\Contracts\CompilerFilterInterface;


class Filter implements CompilerFilterInterface
{
    /**
     * @var array|Contracts\CompilerFilterInterface[]
     */
    private $filters;

    /**
     * @param \Pinepain\SimpleRouting\Contracts\CompilerFilterInterface[] $filters
     */
    public function __construct(array $filters = [])
    {
        $this->setFilters($filters);
    }

    public function getFilters()
    {
        return $this->filters;
    }

    /**
     * @param \Pinepain\SimpleRouting\Contracts\CompilerFilterInterface[] $filters
     */
    public function setFilters(array $filters = [])
    {
        $this->filters = $filters;
    }

    public function filter(array $parsed)
    {
        foreach ($this->getFilters() as $filter) {
            $parsed = $filter->filter($parsed);
        }

        return $parsed;
    }
}
