<?php

$loader = require __DIR__ . "/vendor/autoload.php";

use Pinepain\SimpleRouting\Solutions\SimpleRouter;
use Pinepain\SimpleRouting\Parser;
use Pinepain\SimpleRouting\RoutesCollector;
use Pinepain\SimpleRouting\Compiler;
use Pinepain\SimpleRouting\Filter;
use Pinepain\SimpleRouting\CompilerFilters\Formats;
use Pinepain\SimpleRouting\CompilerFilters\Helpers\FormatsCollection;
use Pinepain\SimpleRouting\RulesGenerator;
use Pinepain\SimpleRouting\Matcher;
use Pinepain\SimpleRouting\FormatsHandler;
use Pinepain\SimpleRouting\FormatHandlers\Path as PathFormatHandler;
use Pinepain\SimpleRouting\UrlGenerator;

$formats_preset = [
    ['segment', '[^/]+', ['default']],
    ['alpha', '[[:alpha:]]+', ['a']],
    ['digit', '[[:digit:]]+', ['d']],
    ['word', '[\w]+', ['w']],
    // see http://stackoverflow.com/questions/19256323/regex-to-match-a-slug
    ['slug', '[a-z0-9]+(?:-[a-z0-9]+)*', ['s']],
    ['path', '.+', 'p'],
];

$collector       = new RoutesCollector(new Parser());
$filter          = new Filter([new Formats(new FormatsCollection($formats_preset))]);
$generator       = new RulesGenerator($filter, new Compiler());
$dispatcher      = new Matcher();
$formats_handler = new FormatsHandler([new PathFormatHandler()]);
$url_generator   = new UrlGenerator($formats_handler);

$router = new SimpleRouter($collector, $generator, $dispatcher, $url_generator);

$router->add('/some/{path}', 'handler');

$router->add('/first/route/{with_param}', 'handler1');
$router->add('/second/{route}/{foo}/{bar}', 'handler2');
$router->add('/route/{with}{/optional?}{/param?default}', 'handler3');


// Url generation example:
echo $router->url('handler1', ['with_param' => 'param-value']), PHP_EOL; // gives us /first/route/param-value
echo $router->url('handler2', ['route' => 'example', 'foo' =>'val', 'bar' => 'given']), PHP_EOL; // gives us /second/example/val/given
echo $router->url('handler3', ['with' => 'some', 'optional' =>'given']), PHP_EOL; // gives us /route/some/given

// When we skip parameters with default value, optinal parameter and the reset of path not included in generated url:
echo $router->url('handler3', ['with' => 'some']), PHP_EOL; // gives us /route/some

// we can force default params insertion:
echo $router->url('handler3', ['with' => 'some', 'optional' => 'without-default'], true), PHP_EOL; // gives us /route/some/without-default

// this one fill fail while we didn't set default value for optional parameter, but asked to generate full url
//echo $router->url('handler3', ['with' => 'some'], true), PHP_EOL; // gives us /route/some/default


// Url routing example:

$url = '/some/homepage';

$result = $router->match($url);

function handler($homepage) {
    var_dump(func_get_args());
}

if (!$result) {
    // not found, do something with it
    throw new RuntimeException("No match found for '{$url}'");
} else {
    list($handler, $variables) = $result;

    // process it, for example

    call_user_func_array($handler, $variables);
}
