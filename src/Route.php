<?php declare(strict_types=1);


namespace Pinepain\SimpleRouting;


use Pinepain\SimpleRouting\Chunks\AbstractChunk;

class Route
{
    /**
     * @var string
     */
    public $handler;
    /**
     * @var AbstractChunk[]
     */
    public $chunks;

    /**
     * @param string $handler
     * @param AbstractChunk[] $chunks
     */
    public function __construct(string $handler, array $chunks)
    {
        $this->handler = $handler;
        $this->chunks = $chunks;
    }
}
