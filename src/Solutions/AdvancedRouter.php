<?php declare(strict_types=1);


namespace Pinepain\SimpleRouting\Solutions;


use Pinepain\SimpleRouting\Match;
use Pinepain\SimpleRouting\Route;

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
     * @var int
     */
    private $trailing_slashes_policy;

    /**
     * @param SimpleRouter $router
     * @param int $trailing_slashes_policy
     */
    public function __construct(SimpleRouter $router, int $trailing_slashes_policy = self::NO_TRAILING_SLASH_POLICY)
    {
        $this->router                  = $router;
        $this->trailing_slashes_policy = $trailing_slashes_policy;
    }

    /**
     * {@inheritdoc}
     */
    public function add(string $route, string $handler): Route
    {
        $route = $this->enforceTrailingSlashPolicy($route, self::ENFORCE_TRAILING_SLASHES_IN_RULE, self::REMOVE_TRAILING_SLASHES_IN_RULE);

        return $this->router->add($route, $handler);
    }

    /**
     * {@inheritdoc}
     */
    public function match(string $url): Match
    {
        $url = $this->enforceTrailingSlashPolicy($url, self::ENFORCE_TRAILING_SLASHES_IN_MATCH, self::REMOVE_TRAILING_SLASHES_IN_MATCH);

        return $this->router->match($url);
    }

    /**
     * {@inheritdoc}
     */
    public function url(string $handler, array $arguments = [], bool $full = false): string
    {
        $generated = $this->router->url($handler, $arguments, $full);

        $generated = $this->enforceTrailingSlashPolicy($generated, self::ENFORCE_TRAILING_SLASHES_IN_URL, self::REMOVE_TRAILING_SLASHES_IN_URL);

        return $generated;
    }

    /**
     * @param string $string
     * @param int    $mask_to_enforce
     * @param int    $mask_to_remove
     *
     * @return string
     */
    protected function enforceTrailingSlashPolicy(string $string, int $mask_to_enforce, int $mask_to_remove): string
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
