<?php

namespace Pinepain\SimpleRouting\CompilerFilters;

use Pinepain\SimpleRouting\Chunks\DynamicChunk;
use Pinepain\SimpleRouting\Chunks\StaticChunk;
use Pinepain\SimpleRouting\CompilerFilters\Helpers\FormatsCollection;
use Pinepain\SimpleRouting\Contracts\CompilerFilterInterface;

class Formats implements CompilerFilterInterface
{
    /**
     * @var FormatsCollection
     */
    private $formats;
    private $default_format;

    public function __construct(FormatsCollection $formats, $default_format = 'default')
    {
        $this->formats        = $formats;
        $this->default_format = $default_format;
    }

    public function handleMissedFormat($name, $format)
    {
        return $format;
    }

    /**
     * @param string[]|DynamicChunk[] $parsed
     *
     * @return array
     */
    public function filter(array $parsed)
    {
        $result = [];

        foreach ($parsed as $chunk) {
            if ($chunk->isStatic()) {
                /** @var StaticChunk $chunk */
                $result[] = $chunk;
                continue;
            }

            /** @var DynamicChunk $chunk */

            $format     = $chunk->format ?: $this->default_format;
            $new_format = $this->formats->find($format) ?: $this->handleMissedFormat($chunk->name, $format);

            $result[] = new DynamicChunk($chunk->name, $new_format, $chunk->default, $chunk->leading_delimiter, $chunk->trailing_delimiter);
        }

        return $result;
    }
}
