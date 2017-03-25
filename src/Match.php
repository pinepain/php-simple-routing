<?php


namespace Pinepain\SimpleRouting;


class Match
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
     * @param array $variables
     */
    public function __construct($handler, array $variables = [])
    {
        $this->handler   = $handler;
        $this->variables = $variables;
    }
}
