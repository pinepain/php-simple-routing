<?php


namespace Pinepain\SimpleRouting;


class Parser
{
    const REGEX_DELIMITER = '~';

    private $parameters_regex = '/
        (?<parameter>
        \{
            \s*
            (?<delimiter>[^\s\w\d:?{}%]+)?    # parameter separator
            \s*
            (?<name>[a-zA-Z_][a-zA-Z0-9_]*) # parameter name
            \s*
            (?:                     # begin of default value group
                \?                      # delimiter
                \s*
                (?<default>([^:{}]*(?:\{(?-1)\}[^:{}]*)*))    # value
            )?                      # end of default value group
            \s*
            (?:                     # begin of format group
              \:                        # delimiter
              \s*
              (?<format>([^{}]*(?:\{(?-1)\}[^{}]*)*)) # format
            )?                      # end of format group
            \s*
        \}
        )
    /xu';

    public function parse($string)
    {
        if (!preg_match_all($this->parameters_regex, $string, $matches, PREG_OFFSET_CAPTURE | PREG_SET_ORDER)) {
            return [$this->lintChunk($string, true)];
        }

        $names = [];

        $chunks = [];
        $prev   = 0;

        foreach ($matches as $id => $match) {
            $offset = $match['parameter'][1];
            $length = mb_strlen($match['parameter'][0]);

            if ($offset > $prev) {
                // match leading static part
                $chunk = $this->getChunk($string, $prev, $offset, false);
            } else {
                $chunk = null;
            }

            $name = $match['name'][0];

            if (isset($names[$name])) {
                $var_offset = $names[$name];
                throw new Exception("Variable '{$name}' already defined at offset {$var_offset}");
            }

            $names[$name] = $offset;

            $delimiter = $match['delimiter'][1] > -1 ? $match['delimiter'][0] : false;
            $default   = isset($match['default']) && $match['default'][1] > -1 ? $match['default'][0] ?: null : false;
            $format    = isset($match['format']) ? $match['format'][0] : false;

            if (false === $default && $delimiter) {
                // optimize rule by appending (or moving) delimiter to previous static path component
                $chunk     = $chunk . $delimiter;
                $delimiter = false;
            }

            if ($chunk) {
                $chunks[] = $chunk;
            }

            $chunks[] = [$name, $format, $default, $delimiter];

            $prev = $offset + $length;
        }

        $offset = mb_strlen($string);

        if ($offset > $prev) {
            $chunk = $this->getChunk($string, $prev, $offset, true);

            $chunks[] = $chunk;
        }

        return $chunks;
    }

    public function getChunk($string, $from, $to, $final = false)
    {
        // match static ending
        $chunk = mb_substr($string, $from, $to - $from);

        $chunk = $this->lintChunk($chunk, $final);
        $chunk = preg_quote($chunk, static::REGEX_DELIMITER);

        return $chunk;
    }

    public function lintChunk($string, $final = false)
    {
        $string = preg_replace('/[\s\v]+/u', '', $string);

        $string = preg_replace('/\/+/', '/', $string);

        if ($final) {
            $string = rtrim($string, '/');
        }

        return $string;
    }
}
