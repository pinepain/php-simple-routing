<?php declare(strict_types=1);


namespace Pinepain\SimpleRouting\Chunks;


class DynamicChunk extends AbstractChunk
{
    /**
     * @param string $name
     * @param string $format
     * @param string|null|bool $default
     * @param string $leading_delimiter
     * @param string $trailing_delimiter
     */
    public function __construct(string $name, string $format = '', $default = false, string $leading_delimiter = '', string $trailing_delimiter = '')
    {
        $this->name               = $name;
        $this->format             = $format;
        $this->default            = $default;
        $this->leading_delimiter  = $leading_delimiter;
        $this->trailing_delimiter = $trailing_delimiter;
    }
}
