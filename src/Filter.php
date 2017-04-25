<?php declare(strict_types=1);


namespace Pinepain\SimpleRouting;


use Pinepain\SimpleRouting\Chunks\AbstractChunk;
use Pinepain\SimpleRouting\Contracts\CompilerFilterInterface;


class Filter implements CompilerFilterInterface
{
    /**
     * @var CompilerFilterInterface[]
     */
    private $filters = [];

    /**
     * @param CompilerFilterInterface[] $filters
     */
    public function __construct(array $filters = [])
    {
        $this->setFilters($filters);
    }

    /**
     * @return CompilerFilterInterface[]
     */
    public function getFilters(): array
    {
        return $this->filters;
    }

    /**
     * @param CompilerFilterInterface[] $filters
     *
     * @return void
     */
    public function setFilters(array $filters = [])
    {
        $this->filters = $filters;
    }

    /**
     * @param AbstractChunk[] $parsed
     *
     * @return AbstractChunk[]
     */
    public function filter(array $parsed): array
    {
        foreach ($this->getFilters() as $filter) {
            $parsed = $filter->filter($parsed);
        }

        return $parsed;
    }
}
