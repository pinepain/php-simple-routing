<?php declare(strict_types=1);


namespace Pinepain\SimpleRouting\Chunks;


class StaticChunk extends AbstractChunk
{
    /**
     * @param string $static
     */
    public function __construct(string $static)
    {
        $this->static = $static;
    }
}
