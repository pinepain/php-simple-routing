<?php declare(strict_types=1);


namespace Pinepain\SimpleRouting;


class Matcher
{
    /**
     * @var Route[]
     */
    private $static_rules = [];
    /**
     * @var Chunk[]
     */
    private $dynamic_rules = [];

    /**
     * @param Route[] $static_rules
     * @param Chunk[] $dynamic_rules
     */
    public function __construct(array $static_rules = [], array $dynamic_rules = [])
    {
        $this->setStaticRules($static_rules);
        $this->setDynamicRules($dynamic_rules);
    }

    /**
     * @param Route[] $static_rules
     * @return void
     */
    public function setStaticRules(array $static_rules)
    {
        $this->static_rules = $static_rules;
    }

    /**
     * @param Chunk[] $dynamic_rules
     * @return void
     */
    public function setDynamicRules(array $dynamic_rules)
    {
        $this->dynamic_rules = $dynamic_rules;
    }

    /**
     * @param string $url
     *
     * @return Match
     */
    public function match(string $url)
    {
        if (isset($this->static_rules[$url])) {
            $route = $this->static_rules[$url];

            return new Match($route->handler);
        }

        return $this->matchDynamicRoute($this->dynamic_rules, $url);
    }

    /**
     * @param Chunk[] $chunks
     * @param string  $url
     *
     * @return Match
     * @throws NotFoundException
     */
    public function matchDynamicRoute(array $chunks, string $url)
    {
        foreach ($chunks as $chunk) {

            if (!preg_match($chunk->regex, $url, $matches)) {
                continue;
            }

            $crumb = $chunk->crumbs[count($matches)];

            $resolved_variables = $this->extractVariablesFromMatches($matches, $crumb->variables);

            return new Match($crumb->handler, $resolved_variables);
        }

        throw new NotFoundException("Url '{$url}' does not match any route");
    }

    /**
     * @param array $matches
     * @param array $variables
     *
     * @return array
     */
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
