<?php


namespace Pinepain\SimpleRouting\Contracts;


interface FormatHandlerInterface
{
    /**
     * @param mixed $value
     *
     * @return mixed
     */
    public function handle($value);
}
