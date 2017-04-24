<?php declare(strict_types=1);

namespace Pinepain\SimpleRouting\CompilerFilters;

use Pinepain\SimpleRouting\Contracts\CompilerFilterInterface;

class Dummy implements CompilerFilterInterface
{
    /**
     * {@inheritdoc}
     */
    public function filter(array $parsed): array
    {
        return $parsed;
    }
}
