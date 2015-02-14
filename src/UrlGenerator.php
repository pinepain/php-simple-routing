<?php


namespace Pinepain\SimpleRouting;


class UrlGenerator
{
    /**
     * @var array
     */
    private $map = array();
    /**
     * @var FormatsHandler
     */
    private $types_handler;

    public function __construct(FormatsHandler $types_handler)
    {
        $this->types_handler = $types_handler;
    }

    public function setMapFromRoutes(array $routes)
    {
        $map = array();

        foreach ($routes as $route => $set) {
            list($handler, $parsed) = $set;

            $map[$handler] = $parsed;
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

    public function generate($handler, array $params = array(), $full = false)
    {
        $map = $this->getMap();

        if (!isset($map[$handler])) {
            return null;
        }

        $parsed = $map[$handler];

        $route = '';

        foreach ($parsed as $chunk) {
            if (is_string($chunk)) {
                // we have url part
                $route .= $this->handleStaticPart($chunk);

                continue;
            }

            // we have parameter definition
            list($name, $format, $default, $delimiter) = $chunk;

            if ($default === false && !isset($params[$name])) {
                throw new Exception("Required parameter '{$name}' value missed");
            }

            if ($default !== false && !isset($params[$name]) && !$full) {
                break;
            }

            $value = isset($params[$name]) ? $params[$name] : $default;

            if (strval($value) !== '0' && empty($value)) {
                if (isset($params[$name])) {
                    throw new Exception("Empty value provided for parameter '{$name}'");
                } else {
                    throw new Exception("Empty default value for parameter '{$name}' set and no other value provided");
                }
            }

            if ($delimiter) {
                $route .= $this->handleStaticPart($delimiter);
            }

            $route .= $this->handleParameter($format, $value, $name);
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
