<?php declare(strict_types=1);


namespace Pinepain\SimpleRouting;


class Crumb
{
    /**
     * @var string
     */
    public $handler;
    /**
     * @var array
     */
    public $variables;

    /**
     * @param string $handler
     * @param array  $variables
     */
    public function __construct(string $handler, array $variables)
    {
        $this->handler   = $handler;
        $this->variables = $variables;
    }
}
