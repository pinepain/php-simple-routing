<?php


namespace Pinepain\SimpleRouting\Contracts;


interface CompilerFilterInterface
{
    /**
     * @param array $parsed
     *
     * @return array
     */
    public function filter(array $parsed);
}