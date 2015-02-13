<?php

namespace Pinepain\SimpleRouting\Filters;

use Pinepain\SimpleRouting\Contracts\CompilerFilterInterface;
use Pinepain\SimpleRouting\Filters\Helpers\FormatsCollection;

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
     * @param array $parsed
     *
     * @return array
     */
    public function filter(array $parsed)
    {
        $result = [];

        foreach ($parsed as $chunk) {
            if (is_string($chunk)) {
                $result[] = $chunk;
                continue;
            }

            list($name, $format, $default, $delimiter) = $chunk;

            $format = $format ?: $this->default_format;

            $new_format = $this->formats->find($format) ?: $this->handleMissedFormat($name, $format);

            $result[] = [$name, $new_format, $default, $delimiter];
        }

        return $result;
    }

}