<?php


namespace Pinepain\SimpleRouting\Chunks;


class StaticChunk extends AbstractChunk
{
    /**
     * @param string $static
     */
    public function __construct($static)
    {
        $this->static = $static;
    }
}
