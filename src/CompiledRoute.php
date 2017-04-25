<?php declare(strict_types=1);


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

    public function __construct(string $regex, array $variables, bool $has_optional)
    {
        $this->regex        = $regex;
        $this->variables    = $variables;
        $this->has_optional = $has_optional;
    }

    /**
     * @return string
     */
    public function getRegex(): string
    {
        return $this->regex;
    }

    /**
     * @return array
     */
    public function getVariables(): array
    {
        return $this->variables;
    }

    /**
     * @return boolean
     */
    public function hasOptional(): bool
    {
        return $this->has_optional;
    }
}
