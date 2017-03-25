<?php


namespace Pinepain\SimpleRouting;


use Pinepain\SimpleRouting\Chunks\AbstractChunk;

class Route
{
    /**
     * @var string
     */
    public $handler;
    /**
     * @var array|AbstractChunk[]
     */
    public $chunks;

    /**
     * @param string $handler
     * @param AbstractChunk[] $chunks
     */
    public function __construct($handler, array $chunks)
    {
        $this->handler = $handler;
        $this->chunks = $chunks;
    }
}
