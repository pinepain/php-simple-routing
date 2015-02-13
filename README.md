# Php Simple Router

[![Build Status](https://travis-ci.org/pinepain/php-simple-router.svg)](https://travis-ci.org/pinepain/php-simple-router)

# About:

This is yet another HTTP request routing implementation which build with simplicity and flexibility in mind. In fact it
is set of tools to build your own HTTP routing stack for your specific needs. 

# Features:

 - best practices from routing experience
 - full unicode support
 - optional parameters
 - custom types
 - flexibility (yeah, someone may say that it is so flexible that you have to give it a support)
 - lose coupled code (at least wannabe)
 - speed (faster than all what you saw before, marketing guys may add "close to bare metal", but that is not true as all we know)

# Example usage:

```php
    <?php
    
    $loader = require __DIR__ . "/vendor/autoload.php";
    
    use Pinepain\SimpleRouting\Solutions\SimpleRouter;
    use Pinepain\SimpleRouting\Parser;
    use Pinepain\SimpleRouting\RoutesCollector;
    use Pinepain\SimpleRouting\Compiler;
    use Pinepain\SimpleRouting\Filter;
    use Pinepain\SimpleRouting\Filters\Formats;
    use Pinepain\SimpleRouting\Filters\Helpers\FormatsCollection;
    use Pinepain\SimpleRouting\RulesGenerator;
    use Pinepain\SimpleRouting\Dispatcher;
    
    $formats_preset = [
        ['segment', '[^/]+', ['default']],
        ['alpha', '[[:alpha:]]+', ['a']],
        ['digit', '[[:digit:]]+', ['d']],
        ['word', '[\w]+', ['w']],
        // see http://stackoverflow.com/questions/19256323/regex-to-match-a-slug
        ['slug', '[a-z0-9]+(?:-[a-z0-9]+)*', ['s']],
    ];
    
    $collector  = new RoutesCollector(new Parser());
    $filter     = new Filter([new Formats(new FormatsCollection($formats_preset))]);
    $generator  = new RulesGenerator($filter, new Compiler());
    $dispatcher = new Dispatcher();
    
    $router = new SimpleRouter($collector, $generator, $dispatcher);
    
    $router->add('/some/{path}', 'handler');
    
    $url = '/some/homepage';
    
    $result = $router->dispatch($url);
    
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
If parameter mandatory mark present, colon is optional.

If parameter segment present but doesn't pass under type format, such situation interpreted as error whole rule will fail.

Here is some examples:

 - `{parameter:digit}`
 - `{parameter:d}`, (assume `d` is short for `digit`)
 - `{parameter?:d}`, this one meand that parameter is optional and has type `digit`. If parameter data will not pass under
    type format, this will be interpreted as error and default value will not be used.

Note, that parameter type regex **must be** valid and **must not** contains capturing groups.

#### Custom parameter type:

Custom type format regexp may be injected in PHP code:

```php

     $formats_preset = [
        ['segment', '[^/]+', ['default']],
        ['alpha', '[[:alpha:]]+', ['a']],
        ['digit', '[[:digit:]]+', ['d']],
        ['word', '[\w]+', ['w']],
        // see http://stackoverflow.com/questions/19256323/regex-to-match-a-slug
        ['slug', '[a-z0-9]+(?:-[a-z0-9]+)*', ['s']],
    ];

    $formats = \Pinepain\SimpleRouting\Filters\Helpers\FormatsCollection($formats_preset); 
```    
you can also manually populate formats:

```php
    $formats->add('dmy_date', '\d{2}-\d{2}-\d{4}', ['dmy']);
```

and later use one of that definition as `/segment/{parameter:dmy_date}` or `/segment/{parameter:dmy}`.

To make all that happens you have to pass formats collection to `Formats` filter and later specify that filter to be
applied to urls:

```php
    $formats_filter = new \Pinepain\SimpleRouting\Filters\Formats($formats);
    $filter = new \Pinepain\SimpleRouting\Filter([$formats_filter]);
    
    // and then pass it to rules data generator,
    
    $generator  = new RulesGenerator($filter, new Compiler());

    $router = new SimpleRouter($collector, $generator, $dispatcher);

    // full example shown above
```


Alternatively, you can in-line type definition into rule:

    /segment/{parameter:\d{2}-\d{2}-\d{4}} 
