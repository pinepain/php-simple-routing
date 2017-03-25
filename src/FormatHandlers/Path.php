<?php

namespace Pinepain\SimpleRouting\FormatHandlers;

use Pinepain\SimpleRouting\Contracts\FormatHandlerInterface;

class Path implements FormatHandlerInterface
{

    /**
     * @param mixed $value
     *
     * @return mixed
     */
    public function handle($value)
    {
        $value      = trim($value, '/');
        $components = explode('/', $value);

        $components = array_map('rawurlencode', $components);

        return implode('/', $components);
    }
}
