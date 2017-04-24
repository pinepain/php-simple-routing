<?php declare(strict_types=1);


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

    /**
     * @param string $format
     * @param string $value
     *
     * @return string
     */
    public function handle(string $format, $value): string
    {
        if (isset($this->handlers[$format])) {
            return $this->handlers[$format]->handle($value);
        }

        return $this->handleDefault($value);
    }

    /**
     * @param string $value
     *
     * @return string
     */
    public function handleDefault(string $value): string
    {
        return rawurlencode($value);
    }
}
