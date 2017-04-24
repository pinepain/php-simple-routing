<?php declare(strict_types=1);


namespace Pinepain\SimpleRouting\Contracts;


use Pinepain\SimpleRouting\Chunks\AbstractChunk;


interface CompilerFilterInterface
{
    /**
     * @param AbstractChunk[] $parsed
     *
     * @return AbstractChunk[]
     */
    public function filter(array $parsed): array;
}
