<?php declare(strict_types=1);

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
    /**
     * @var string
     */
    private $default_format;

    public function __construct(FormatsCollection $formats, string $default_format = 'default')
    {
        $this->formats        = $formats;
        $this->default_format = $default_format;
    }

    public function handleMissedFormat(string $name, string $format): string
    {
        return $format;
    }

    /**
     * {@inheritdoc}
     */
    public function filter(array $parsed): array
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
