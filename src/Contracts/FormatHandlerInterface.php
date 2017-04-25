<?php declare(strict_types=1);


namespace Pinepain\SimpleRouting\Contracts;


interface FormatHandlerInterface
{
    /**
     * @param string $value
     *
     * @return string
     */
    public function handle(string $value);
}
