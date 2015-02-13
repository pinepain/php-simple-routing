<?php


namespace Pinepain\SimpleRouting;


class Compiler
{
    const REGEX_DELIMITER = '~';

    public function compile(array $parsed)
    {
        $regex          = [];
        $optional_stack = [];
        $variables      = [];

        foreach ($parsed as $id => $chunk) {
            if (is_string($chunk)) {
                // we have url part
                $regex[$id] = preg_quote($chunk, static::REGEX_DELIMITER);

                continue;
            }

            // we have parameter definition
            list($name, $format, $default, $delimiter) = $chunk;

            $param_regex = '(' . ($format ?: '[^/]+') . ')';

            if ($default !== false) {
                if ($delimiter) {
                    $param_regex      = '(?:' . preg_quote($delimiter, static::REGEX_DELIMITER) . $param_regex;
                    $optional_stack[] = ')?';
                } else {
                    $optional_stack[] = '?';
                }
            } elseif ($delimiter) {
                $param_regex = preg_quote($delimiter, static::REGEX_DELIMITER) . $param_regex;
            }

            $regex[$id]       = $param_regex;
            $variables[$name] = $default;
        }

        $regex = array_merge($regex, $optional_stack);

        $regex = implode('', $regex);

        return new CompiledRoute($regex, $variables, !empty($optional_stack));
    }

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