<?php


namespace Pinepain\SimpleRouting;


use Pinepain\SimpleRouting\Chunks\AbstractChunk;
use Pinepain\SimpleRouting\Chunks\DynamicChunk;
use Pinepain\SimpleRouting\Chunks\StaticChunk;

class UrlGenerator
{
    /**
     * @var AbstractChunk[][]
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

    public function setMapFromRoutesCollector(RoutesCollector $collector)
    {
        $map = [];

        foreach ($collector->getDynamicRoutes() as $route) {
            $map[$route->handler] = $route->chunks;
        }

        foreach ($collector->getStaticRoutes() as $route) {
            $map[$route->handler] = $route->chunks;
        }

        $this->setMap($map);
    }

    public function setMap(array $map)
    {
        $this->map = $map;
    }

    public function getMap()
    {
        return $this->map;
    }

    /**
     * Generate URL
     *
     * @param string $handler Route definition identifier
     * @param array $params Route parameter values
     * @param bool $full Whether missed optional parameters should be included in built URL
     *
     * @return string Generated URL
     * @throws Exception
     */
    public function generate($handler, array $params = [], $full = false)
    {
        $map = $this->getMap();

        if (!isset($map[$handler])) {
            return null;
        }

        $parsed = $map[$handler];

        $route = '';

        foreach ($parsed as $chunk) {
            if ($chunk->isStatic()) {
                /** @var StaticChunk $chunk */
                $route .= $this->handleStaticPart($chunk->static);

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

            // TODO: handle leading and trailing delimiter
            if ($chunk->leading_delimiter) {
                $route .= $this->handleStaticPart($chunk->leading_delimiter);
            }

            $route .= $this->handleParameter($chunk->format, $value, $chunk->name);

            if ($chunk->trailing_delimiter) {
                $route .= $this->handleStaticPart($chunk->trailing_delimiter);
            }
        }

        return $route;
    }

    public function handleStaticPart($chunk)
    {
        // handle single segments delimiter
        if ($chunk == '/') {
            return $chunk;
        }

        return implode('/', array_map('rawurlencode', explode('/', $chunk)));
    }

    public function handleParameter($format, $value, $name)
    {
        return $this->types_handler->handle($format, $value);
    }
}
