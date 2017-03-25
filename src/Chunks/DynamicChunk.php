<?php


namespace Pinepain\SimpleRouting\Chunks;


class DynamicChunk extends AbstractChunk
{
    /**
     * @param string $name
     * @param string|bool $format
     * @param string|null|bool $default
     * @param string|bool $leading_delimiter
     * @param string|bool $trailing_delimiter
     */
    public function __construct($name, $format = false, $default = false, $leading_delimiter = false, $trailing_delimiter = false)
    {
        $this->name               = $name;
        $this->format             = $format;
        $this->default            = $default;
        $this->leading_delimiter  = $leading_delimiter;
        $this->trailing_delimiter = $trailing_delimiter;
    }
}
