<?php


namespace Pinepain\SimpleRouting\CompilerFilters\Helpers;


class FormatsCollection
{
    public $formats = [];
    private $aliases = [];

    public function __construct(array $preset = array())
    {
        foreach ($preset as $args) {
            call_user_func_array([$this, 'add'], $args);
        }
    }

    public function add($name, $regex, $alias = [])
    {
        $this->formats[$name] = $regex;

        foreach ((array)$alias as $a) {
            $this->aliases[$a] = $name;
        }
    }

    public function remove($name)
    {
        unset($this->formats[$name]);

        $this->aliases = array_filter($this->aliases, function ($value) use ($name) {
            return $value !== $name;
        });
    }

    public function find($name)
    {
        if (isset($this->aliases[$name])) {
            return $this->formats[$this->aliases[$name]];
        }

        if (isset($this->formats[$name])) {
            return $this->formats[$name];
        }

        return null;
    }
}
