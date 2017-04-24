<?php declare(strict_types=1);


namespace Pinepain\SimpleRouting\Chunks;


abstract class AbstractChunk
{
    /**
     * @var string
     */
    public $static = '';

    /**
     * @var string
     */
    public $name = '';
    /**
     * @var string
     */
    public $format = '';
    /**
     * @var bool|null|string
     */
    public $default;
    /**
     * @var string
     */
    public $leading_delimiter = '';
    /**
     * @var string
     */
    public $trailing_delimiter = '';

    public function isStatic(): bool
    {
        return '' === $this->name;
    }
}
