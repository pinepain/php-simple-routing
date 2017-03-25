<?php


namespace Pinepain\SimpleRouting\Solutions;


class AdvancedRouter implements RouterInterface
{
    const NO_TRAILING_SLASH_POLICY = 0;

    const ENFORCE_TRAILING_SLASHES_IN_RULE = 1;
    const REMOVE_TRAILING_SLASHES_IN_RULE = 2;

    const ENFORCE_TRAILING_SLASHES_IN_MATCH = 4;
    const REMOVE_TRAILING_SLASHES_IN_MATCH = 8;

    const ENFORCE_TRAILING_SLASHES_IN_URL = 16;
    const REMOVE_TRAILING_SLASHES_IN_URL = 32;

    const ENFORCE_TRAILING_SLASH = self::ENFORCE_TRAILING_SLASHES_IN_RULE | self::ENFORCE_TRAILING_SLASHES_IN_MATCH | self::ENFORCE_TRAILING_SLASHES_IN_URL;
    const REMOVE_TRAILING_SLASH = self::REMOVE_TRAILING_SLASHES_IN_RULE | self::REMOVE_TRAILING_SLASHES_IN_MATCH | self::REMOVE_TRAILING_SLASHES_IN_URL;

    /**
     * @var SimpleRouter
     */
    private $router;
    /**
     * @var null
     */
    private $trailing_slashes_policy;

    /**
     * @param SimpleRouter $router
     * @param int $trailing_slashes_policy
     */
    public function __construct(SimpleRouter $router, $trailing_slashes_policy = self::NO_TRAILING_SLASH_POLICY)
    {
        $this->router                  = $router;
        $this->trailing_slashes_policy = $trailing_slashes_policy;
    }

    /**
     * {@inheritdoc}
     */
    public function add($route, $handler)
    {
        $route = $this->enforceTrailingSlashPolicy($route, self::ENFORCE_TRAILING_SLASHES_IN_RULE, self::REMOVE_TRAILING_SLASHES_IN_RULE);

        return $this->router->add($route, $handler);
    }

    /**
     * {@inheritdoc}
     */
    public function match($url)
    {
        $url = $this->enforceTrailingSlashPolicy($url, self::ENFORCE_TRAILING_SLASHES_IN_MATCH, self::REMOVE_TRAILING_SLASHES_IN_MATCH);

        return $this->router->match($url);
    }

    /**
     * {@inheritdoc}
     */
    public function url($handler, array $arguments = [], $full = false)
    {
        $generated = $this->router->url($handler, $arguments, $full);

        $generated = $this->enforceTrailingSlashPolicy($generated, self::ENFORCE_TRAILING_SLASHES_IN_URL, self::REMOVE_TRAILING_SLASHES_IN_URL);

        return $generated;
    }

    protected function enforceTrailingSlashPolicy($string, $mask_to_enforce, $mask_to_remove)
    {
        if ($mask_to_enforce & $this->trailing_slashes_policy) {
            return rtrim($string, '/') . '/';
        }

        if ($mask_to_remove & $this->trailing_slashes_policy) {
            return rtrim($string, '/');
        }

        return $string;
    }
}
