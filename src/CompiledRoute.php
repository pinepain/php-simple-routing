<?php


namespace Pinepain\SimpleRouting;


class CompiledRoute
{
    /**
     * @var string
     */
    private $regex;
    /**
     * @var array
     */
    private $variables;

    /**
     * @var bool
     */
    private $has_optional;

    public function __construct($regex, $variables, $has_optional)
    {
        $this->regex        = $regex;
        $this->variables    = $variables;
        $this->has_optional = $has_optional;
    }

    /**
     * @return string
     */
    public function getRegex()
    {
        return $this->regex;
    }

    /**
     * @return array
     */
    public function getVariables()
    {
        return $this->variables;
    }

    /**
     * @return boolean
     */
    public function hasOptional()
    {
        return $this->has_optional;
    }
}
