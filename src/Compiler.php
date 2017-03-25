<?php


namespace Pinepain\SimpleRouting;


use Pinepain\SimpleRouting\Chunks\AbstractChunk;
use Pinepain\SimpleRouting\Chunks\DynamicChunk;
use Pinepain\SimpleRouting\Chunks\StaticChunk;

class Compiler
{
    const REGEX_DELIMITER = '~';

    /**
     * @param AbstractChunk[] $parsed
     *
     * @return CompiledRoute
     */
    public function compile(array $parsed)
    {
        $regex          = [];
        $optional_stack = [];
        $variables      = [];

        // TODO: optimize

        foreach ($parsed as $id => $chunk) {
            if ($chunk->isStatic()) {
                /** @var StaticChunk $chunk */
                $regex[$id] = preg_quote($chunk->static, static::REGEX_DELIMITER);
                continue;
            }
            /** @var DynamicChunk $chunk */

            $param_regex = '(' . ($chunk->format ?: '[^/]+') . ')';

            if ($chunk->default !== false) {
                $group = false;

                if ($chunk->leading_delimiter) {
                    $group       = true;
                    $param_regex = '(?:' . preg_quote($chunk->leading_delimiter, static::REGEX_DELIMITER) . $param_regex;
                }

                if ($chunk->trailing_delimiter) {
                    if (!$group) {
                        $group       = true;
                        $param_regex = '(?:' . $param_regex;
                    }

                    $param_regex .= preg_quote($chunk->trailing_delimiter, static::REGEX_DELIMITER);
                }

                $optional_stack[] = $group ? ')?' : '?';
            } else {
                if ($chunk->leading_delimiter) {
                    $param_regex = preg_quote($chunk->leading_delimiter, static::REGEX_DELIMITER) . $param_regex;
                }

                if ($chunk->trailing_delimiter) {
                    $param_regex .= preg_quote($chunk->trailing_delimiter, static::REGEX_DELIMITER);
                }

            }

            $regex[$id]              = $param_regex;
            $variables[$chunk->name] = $chunk->default;
        }

        $regex = array_merge($regex, $optional_stack);

        $regex = implode('', $regex);

        return new CompiledRoute($regex, $variables, !empty($optional_stack));
    }

    /**
     * @param $name
     * @param $format_regex
     *
     * @throws Exception
     * @return null
     */
    public function validateFormat($name, $format_regex)
    {
        // validate regex and check for nested catching groups
        $re_test_syntax = "~{$format_regex}~x";

        $result = @preg_match($re_test_syntax, 'test', $matches_test_syntax);

        if ($result === false) {
            throw new Exception("Invalid format regex '{$format_regex}' for variable '{$name}'");
        }

        $re_test_groups = "~(?:{$format_regex})|()~x";

        preg_match($re_test_groups, 'test', $matches_test_groups);

        //var_dump($matches_test_syntax, $matches_test_groups);
        if (count($matches_test_syntax) > 1 || count($matches_test_groups) > 2) {
            throw new Exception("Catching groups in regex '{$format_regex}' for variable '{$name}' detected");
        }
    }
}
