<?php declare(strict_types=1);


namespace Pinepain\SimpleRouting;


use Pinepain\SimpleRouting\Chunks\AbstractChunk;
use Pinepain\SimpleRouting\Chunks\DynamicChunk;
use Pinepain\SimpleRouting\Chunks\StaticChunk;

class Parser
{
    const REGEX_DELIMITER = '~';

    /**
     * @var string
     */
    private $parameters_regex = '/
        (?<parameter>
        \{
            \s*
            (?<leading_delimiter>[^\s\w\d:?{}%]+)?    # leading parameter separator
            \s*
            (?<name>[a-zA-Z_][a-zA-Z0-9_-]*) # parameter name
            \s*
            (?<trailing_delimiter>[^\s\w\d:?{}%]+)?    # trailing parameter separator
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

    /**
     * Parse route rule
     *
     * @param string $string Route rule string to parse
     *
     * @return AbstractChunk[] Array of parsed chunks
     * @throws Exception
     */
    public function parse($string)
    {
        if (!preg_match_all($this->parameters_regex, $string, $matches, PREG_OFFSET_CAPTURE | PREG_SET_ORDER)) {

            $chunk = new StaticChunk($this->lintChunk($string, true));

            return [$chunk];
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

            $leading_delimiter  = $match['leading_delimiter'][1] > -1 ? $match['leading_delimiter'][0] : '';
            $trailing_delimiter = '';
            if (isset($match['trailing_delimiter'])) {
                $trailing_delimiter = $match['trailing_delimiter'][1] > -1 ? $match['trailing_delimiter'][0] : '';
            }

            $default = isset($match['default']) && $match['default'][1] > -1 ? $match['default'][0] ?: null : false;
            $format  = $match['format'][0] ?? '';

            if ($chunk) {
                $chunks[] = new StaticChunk($chunk);
            }

            $dynamic_chunk = new DynamicChunk($name, $format, $default, $leading_delimiter, $trailing_delimiter);

            $chunks[] = $dynamic_chunk;

            $prev = $offset + $length;
        }

        $offset = (int)mb_strlen($string);

        if ($offset > $prev) {
            $chunk = $this->getChunk($string, $prev, $offset, true);

            $chunks[] = new StaticChunk($chunk);
        }

        return $chunks;
    }

    /**
     * Get chunk from route rule string specified with given position
     *
     * @param string $string Route rule string
     * @param int    $from   Chunk start position
     * @param int    $to     Chunk end position
     * @param bool   $final  Whether chunk is final in given rule
     *
     * @return string
     */
    public function getChunk($string, $from, $to, $final = false)
    {
        // match static ending
        $chunk = mb_substr($string, $from, $to - $from);

        $chunk = $this->lintChunk($chunk, $final);

        return $chunk;
    }

    /**
     * Lint chunk string
     *
     * @param string $string
     * @param bool   $final
     *
     * @return string
     */
    public function lintChunk($string, $final = false)
    {
        $string = preg_replace('/[\s\v]+/u', '', $string);

        $string = preg_replace('/\/+/', '/', $string);

        return $string;
    }
}
