<?php declare(strict_types=1);


namespace Pinepain\SimpleRouting;


class Chunk
{
    /**
     * @var string
     */
    public $regex;
    /**
     * @var Crumb[]
     */
    public $crumbs;

    /**
     * @param string  $regex
     * @param Crumb[] $crumbs
     */
    public function __construct(string $regex, array $crumbs)
    {
        $this->regex  = $regex;
        $this->crumbs = $crumbs;
    }
}
