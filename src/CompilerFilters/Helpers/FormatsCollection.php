<?php declare(strict_types=1);


namespace Pinepain\SimpleRouting\CompilerFilters\Helpers;


class FormatsCollection
{
    /**
     * @var string[]
     */
    public $formats = [];
    /**
     * @var string[]
     */
    private $aliases = [];

    public function __construct(array $preset = [])
    {
        foreach ($preset as $args) {
            call_user_func_array([$this, 'add'], $args);
        }
    }

    /**
     * @param string   $name
     * @param string   $regex
     * @param string[] $aliases
     *
     * @return void
     */
    public function add(string $name, string $regex, array $aliases = [])
    {
        $this->formats[$name] = $regex;

        foreach ((array)$aliases as $a) {
            $this->aliases[$a] = $name;
        }
    }

    /**
     * @param string $name
     *
     * @return void
     */
    public function remove(string$name)
    {
        unset($this->formats[$name]);

        $this->aliases = array_filter($this->aliases, function ($value) use ($name): bool {
            return $value !== $name;
        });
    }

    /**
     * @param string $name
     *
     * @return string
     */
    public function find(string $name): string
    {
        if (isset($this->aliases[$name])) {
            return $this->formats[$this->aliases[$name]];
        }

        if (isset($this->formats[$name])) {
            return $this->formats[$name];
        }

        return '';
    }
}
