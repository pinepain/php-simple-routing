<?php


namespace Pinepain\SimpleRouting;


use Pinepain\SimpleRouting\Contracts\FormatHandlerInterface;


class FormatsHandler
{

    /** @var FormatHandlerInterface[] */
    private $handlers;

    /**
     * @param FormatHandlerInterface[] $handlers
     */
    public function __construct(array $handlers = [])
    {
        $this->handlers = $handlers;
    }

    public function handle($format, $value)
    {
        if (isset($this->handlers[$format])) {
            return $this->handlers[$format]->handle($value);
        }

        return $this->handleDefault($value);
    }

    public function handleDefault($value)
    {
        return rawurlencode($value);
    }
}
