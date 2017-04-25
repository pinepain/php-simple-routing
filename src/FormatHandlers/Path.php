<?php declare(strict_types=1);

namespace Pinepain\SimpleRouting\FormatHandlers;

use Pinepain\SimpleRouting\Contracts\FormatHandlerInterface;

class Path implements FormatHandlerInterface
{
    /**
     * {@inheritdoc}
     */
    public function handle(string $value): string
    {
        $value      = trim($value, '/');
        $components = explode('/', $value);

        $components = array_map('rawurlencode', $components);

        return implode('/', $components);
    }
}
