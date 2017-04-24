<?php declare(strict_types=1);


namespace Pinepain\SimpleRouting;


use Pinepain\SimpleRouting\Chunks\AbstractChunk;
use Pinepain\SimpleRouting\Chunks\DynamicChunk;
use Pinepain\SimpleRouting\Chunks\StaticChunk;

class UrlGenerator
{
    /**
     * @var Route[]
     */
    private $map = [];

    /**
     * @var FormatsHandler
     */
    private $types_handler;

    public function __construct(FormatsHandler $types_handler)
    {
        $this->types_handler = $types_handler;
    }

    /**
     * @param RoutesCollector $collector
     *
     * @return void
     */
    public function setMapFromRoutesCollector(RoutesCollector $collector)
    {
        $map = [];

        foreach ($collector->getDynamicRoutes() as $route) {
            $map[$route->handler] = $route;
        }

        foreach ($collector->getStaticRoutes() as $route) {
            $map[$route->handler] = $route;
        }

        $this->setMap($map);
    }

    /**
     * @param Route[] $map
     *
     * @return void
     */
    public function setMap(array $map)
    {
        $this->map = $map;
    }

    /**
     * @return Route[]
     */
    public function getMap(): array
    {
        return $this->map;
    }

    /**
     * Generate URL
     *
     * @param string $handler Route definition identifier
     * @param array  $params  Route parameter values
     * @param bool   $full    Whether missed optional parameters should be included in built URL
     *
     * @return string Generated URL
     * @throws Exception
     * @throws NotFoundException
     */
    public function generate(string $handler, array $params = [], $full = false)
    {
        $map = $this->getMap();

        if (!isset($map[$handler])) {
            throw new NotFoundException("Handler '{$handler}' mapping does not exist");
        }

        $route = $map[$handler];

        $url = '';

        foreach ($route->chunks as $chunk) {
            if ($chunk->isStatic()) {
                /** @var StaticChunk $chunk */
                $url .= $this->handleStaticPart($chunk->static);

                continue;
            }

            /** @var DynamicChunk $chunk */

            if ($chunk->default === false && !isset($params[$chunk->name])) {
                throw new Exception("Required parameter '{$chunk->name}' value missed");
            }

            if ($chunk->default !== false && !isset($params[$chunk->name]) && !$full) {
                break;
            }

            $value = isset($params[$chunk->name]) ? $params[$chunk->name] : $chunk->default;

            if (strval($value) !== '0' && empty($value)) {
                if (isset($params[$chunk->name])) {
                    throw new Exception("Empty value provided for parameter '{$chunk->name}'");
                } else {
                    throw new Exception("Empty default value for parameter '{$chunk->name}' set and no other value provided");
                }
            }

            if ($chunk->leading_delimiter) {
                $url .= $this->handleStaticPart($chunk->leading_delimiter);
            }

            $url .= $this->handleParameter($chunk->format, $value, $chunk->name);

            if ($chunk->trailing_delimiter) {
                $url .= $this->handleStaticPart($chunk->trailing_delimiter);
            }
        }

        return $url;
    }

    /**
     * @param string $chunk
     *
     * @return string
     */
    public function handleStaticPart(string $chunk): string
    {
        // handle single segments delimiter
        if ($chunk == '/') {
            return $chunk;
        }

        return implode('/', array_map('rawurlencode', explode('/', $chunk)));
    }

    /**
     * @param string $format
     * @param string $value
     * @param string $name
     *
     * @return string
     */
    public function handleParameter(string $format, string $value, string $name)
    {
        return $this->types_handler->handle($format, $value);
    }
}
