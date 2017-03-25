<?php


namespace Pinepain\SimpleRouting\Chunks;


abstract class AbstractChunk
{
    /**
     * @var string|null
     */
    public $static;

    /**
     * @var string|null
     */
    public $name;
    /**
     * @var bool|string
     */
    public $format;
    /**
     * @var bool|null|string
     */
    public $default;
    /**
     * @var bool|string
     */
    public $leading_delimiter;
    /**
     * @var bool|string
     */
    public $trailing_delimiter;

    public function isStatic()
    {
        return null === $this->name;
    }
}
