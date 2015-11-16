# Php Simple Router

[![Build Status](https://travis-ci.org/pinepain/php-simple-routing.svg)](https://travis-ci.org/pinepain/php-simple-routing)

# About:

This is yet another HTTP request routing implementation which build with simplicity and flexibility in mind. In fact it
is set of tools to build your own HTTP routing stack for your specific needs. 

In this project I compiled best known experience in routing by combining simplicity, flexibility and speed.
Special thanks to [Nikita Popov](https://github.com/nikic) an his [FastRoute](https://github.com/nikic/FastRoute) routing engine implementation and his
perfect [blog post explaining how the implementation works and why it is fast](http://nikic.github.io/2014/02/18/Fast-request-routing-using-regular-expressions.html).

I hope we can merge my features to his implementation, but as FastRoute is experimental (at some parts) stuff and tries
to do full routing cycle (playing with HTTP request method, which I prefer let to end-user) it may take a while.
 
There is `bench.php` file which gives you a brief numbers how fast this implementation to other implementations. You can
run `composer require "nikic/fast-route : *@dev" && php bench.php` to compare with FastRoute implementation which is the
one of the fastest. Some performance degradation (about 10%) probably is the result of extra code related to extended 
parameters support (optional parameters, default values, etc.).

# Features:

 - best practices from routing experience
 - full unicode support
 - optional parameters
 - custom types
 - flexibility (yeah, someone may say that it is so flexible that you have to give it a support)
 - lose coupled code (at least wannabe)
 - speed (one of fastest from all what you've ever seen, marketing guys may add "close to bare metal", but that is not true as all we know)

# Example usage:

```php
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
```
    
    
*Note: While this framework is rather set of blocks it was built with mind and hope that you will use some IoC container,
but you can do all that manually.*


# Urls generation:

In basic implementation we use `handler` value for routes identification. Same `handler` value for multiple routes will
lead to situation, when url will be generated based on last added route.

## Example:

For example, we have `SimpleRouter` initialized as written in example above, then, populate it with extra routes:

```php
    
    $router->add('/first/route/{with_param}', 'handler1');
    $router->add('/second/{route}/{foo}/{bar}', 'handler2');
    $router->add('/route/{with}{/optional?}{/param?default}', 'handler3');

```

now we can generate some urls:

```php

    echo $router->url('handler1', ['with_param' => 'param-value']), PHP_EOL; // gives us /first/route/param-value
    echo $router->url('handler2', ['route' => 'example', 'foo' =>'val', 'bar' => 'given']), PHP_EOL; // gives us /second/example/val/given
    echo $router->url('handler3', ['with' => 'some', 'optional' =>'given']), PHP_EOL; // gives us /route/some/given
    
    // When we skip parameters with default value, optinal parameter and the reset of path not included in generated url:
    echo $router->url('handler3', ['with' => 'some']), PHP_EOL; // gives us /route/some
    
    // we can force default params insertion:
    echo $router->url('handler3', ['with' => 'some', 'optional' => 'without-default'], true), PHP_EOL; // gives us /route/some/without-default
    
    // this one fill fail while we didn't set default value for optional parameter, but asked to generate full url
    //echo $router->url('handler3', ['with' => 'some'], true), PHP_EOL; // gives us /route/some/default

```

By default route params limited to single segment (placed between slashes ('/')), so by default raw slash is not permitted in
parameter value and goes encoded. As well as any non-url-safe characters. To change that behavior, you can define custom
types handlers which will form param value as needed.

Empty values for parameter values are not permitted.

# Rules syntax:

## Route parameters:

You can capture segments of the request URI within your route with named parameters. Parameters description are written
within curl brackets (`{` and `}`) and consist from parameter name, parameter  default value and parameter type.

Nested parameters are not supported, use groups instead.

### Parameter name:

Valid parameter name should start from letter or underscore and can contain alphanumeric, underscore and dash characters,
no spaces allowed. It can be described with regexp: `[a-zA_Z_][a-zA_Z0-9_]*`.

These are valid parameter names:

 - `{valid_parameter}`
 - `{alsoValid}`
 - `{_foo}`
 - `{_}`
 - `{bar_}`
 - `{bar123}`

And these are not:

 - `{i-am-invalid}`
 - `{1slug}`
 - `{}`
 - `{no spaces allowed}`
 - `{önly_alphänümeric_allowed}`


Note, that leading underscore in parameter name lead under certain circumstances in user-land code to intersection with
PHP magic methods. Though it is less likely, you've being warned.

### Optional parameters and default values:

By default all parameters are mandatory. To mark route parameter is optional add question mark (`?`) after it name with 
parameter default value:

    {i_am_optional?missed}
    
Note, that default value is also optional and if not given, `null` will be used instead:
 
    {i_am_optional_and_null_by_default?}

It is often desired to have optional parameter separator, for example, slash (`/`), optional too. To do so, you can embed any
punctuation character, except curly brackets (`{`, `}`), colon (`:`), percent (`%`) and question mark (`?`) (anyway,
question mark is not a part of valid URL path):

 - `/some/route{/optional?}` will match `/some/route/value` and `/some/route`
 - but `/some/route/{optional?}` will match `/some/route/value` and `/some/route/`, but not `/some/route`, and due to URL
   normalization (removing repeating slashes, trimming closing slash, etc.) such rule will fail for all URLs without
   optional parameter.

Everything after optional parameter, will be optional too. Mandatory parameters after optional become mandatory only if
optional parameter given. When mandatory parameter given after optional and optional one not given, mandatory parameter
value will be set to default one - to `null`. Tip: it may be a good idea to keep optional all parameters after first
optional one:

 - `/static{/optional?}/{mandatory}` on path `/static` will lead to `optional=null` and `mandatory=null`
 - `/static{/optional?}/{mandatory}` on path `/static/some-value` will fail while it is expected to have slash and value
   for mandatory parameter (something like `/mandatory-value`)
 - `/static{/optional?}/{mandatory}` on path `/static/some-value/mandatory-value` will succeeds with `optional=some-value`
   and `mandatory=mandatory-value`
 - `/static{/optional?}{/optional_too?}` on path `/static/some-value` will lead to `optional=some-value` and `optional_too=null`
  and on path `/static/some-value/more-values` will lead to `optional=some-value` and `optional_too=more-values`
  
   
### Parameter type:

By default parameter is whole URI segment (part between slashes):

 - `/this-is-segment/i-am-also-segment/`
 - `/123/numbers_are_segments_too`
 - `/セグメント/unicode-is-ok`
 
You can limit that by specifying parameter type by placing colon (`:`) and type definition after parameter name.
If parameter optional mark present (question mark `?`) and no default value specified, colon is optional.

If parameter segment present but doesn't pass under type format, whole rule will no match.

Here is some examples:

 - `{parameter:\d+}`
 - `{parameter?:\w{2}-\d+}`, this one meand that parameter is optional and has type regexp `\w{2}-\d+`.
 
Note, that parameter type regex **must be** valid and **must not** contains capturing groups.

#### Custom parameter type:

Custom type format regexp may be injected in PHP code:

```php
    use \Pinepain\SimpleRouting\CompilerFilters\Helpers\FormatsCollection;
    
    $formats_preset = [
        ['segment', '[^/]+', ['default']],
        ['alpha', '[[:alpha:]]+', ['a']],
        ['digit', '[[:digit:]]+', ['d']],
        ['word', '[\w]+', ['w']],
        // see http://stackoverflow.com/questions/19256323/regex-to-match-a-slug
        ['slug', '[a-z0-9]+(?:-[a-z0-9]+)*', ['s']],
        ['path', '.+', 'p'],
    ];
    
    $formats = FormatsCollection($formats_preset); 
```    
So that later you can use something like:

 - `{parameter:digit}`
 - `{parameter:d}`, (assume `d` is short for `digit`)
 - `{parameter?:d}`, this one mean that parameter is optional and has type `digit`. 

You can also manually populate formats:

```php
    $formats->add('dmy_date', '\d{2}-\d{2}-\d{4}', ['dmy']);
```

and later use one of that definition as `/segment/{parameter:dmy_date}` or `/segment/{parameter:dmy}`.

To make all that happens you have to pass formats collection to `Formats` filter and later specify that filter to be
applied to urls:

```php
    use \Pinepain\SimpleRouting\CompilerFilters\Formats;
    use \Pinepain\SimpleRouting\Filter;

    $formats_filter = new Formats($formats);
    $filter = new Filter([$formats_filter]);
    
    // and then pass it to rules data generator,
    
    $generator  = new RulesGenerator($filter, new Compiler());

    $router = new SimpleRouter($collector, $generator, $dispatcher);

    // full example shown above
```


Alternatively, you can in-line type definition into rule:

    /segment/{parameter:\d{2}-\d{2}-\d{4}} 

