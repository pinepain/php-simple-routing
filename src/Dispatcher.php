<?php


namespace Pinepain\SimpleRouting;


class Dispatcher
{
    /**
     * @var array
     */
    private $static_rules;
    /**
     * @var array
     */
    private $dynamic_rules;

    /**
     * @param array $static_rules
     * @param array $dynamic_rules
     */
    public function __construct(array $static_rules = [], array $dynamic_rules = [])
    {
        $this->setStaticRules($static_rules);
        $this->setDynamicRules($dynamic_rules);
    }

    public function setStaticRules(array $static_rules)
    {
        $this->static_rules = $static_rules;
    }

    public function setDynamicRules(array $dynamic_rules)
    {
        $this->dynamic_rules = $dynamic_rules;
    }

    public function dispatch($url)
    {
        if (isset($this->static_rules[$url])) {
            return $this->static_rules[$url];
        }

        return $this->dispatchDynamicRoute($this->dynamic_rules, $url);
    }

    public function dispatchDynamicRoute($rules, $url)
    {
        foreach ($rules as list($regex, $map)) {
            if (!preg_match($regex, $url, $matches)) {
                continue;
            }

            list ($handler, $variables) = $map[count($matches)];

            $resolved_variables = $this->extractVariablesFromMatches($matches, $variables);

            return [$handler, $resolved_variables];
        }

        return null;
    }

    public function extractVariablesFromMatches(array $matches, array $variables)
    {
        $result = [];
        $pos    = 1;

        foreach ($variables as $name => $default) {
            $result[$name] = isset($matches[$pos]) && $matches[$pos] ? $matches[$pos] : $default;

            $pos++;
        }

        return $result;
    }

}