<?php

namespace Pinepain\SimpleRouting\CompilerFilters;

use Pinepain\SimpleRouting\Contracts\CompilerFilterInterface;

class Dummy implements CompilerFilterInterface
{
    public function filter(array $parsed)
    {
        return $parsed;
    }
}
