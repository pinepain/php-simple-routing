<?php
$loader = require __DIR__ . "/vendor/autoload.php";

// Composer requirement:
//      "aura/router":"2.0.*@dev",
//      "symfony/routing": "3.0.*@dev",
//      "nikic/fast-route": "dev-master"


// composer require "aura/router : 2.0.*@dev"
// composer require "symfony/routing : 3.0.*@dev"
// composer require "nikic/fast-route : *@dev"

function dd()
{
    call_user_func_array('var_dump', func_get_args());
    die;
}

function str_random($length = 16)
{
    if (!function_exists('openssl_random_pseudo_bytes')) {
        throw new RuntimeException('OpenSSL extension is required.');
    }

    $bytes = openssl_random_pseudo_bytes($length * 2);

    if ($bytes === false) {
        throw new RuntimeException('Unable to generate random string.');
    }

    return substr(str_replace(array('/', '+', '='), '', base64_encode($bytes)), 0, $length);
}

function generateRoute($parameters = 1, $optional = 0, $with_default = false, $missed = 0)
{
    $example = $route = '/' . str_random(8) . '/' . str_random(8);


    while ($parameters - $optional > 0) {
        $route .= '/{x_' . str_random(8) . '}';
        $example .= '/' . str_random(8);
        $parameters--;
    }

    while ($optional > 0) {
        $route .= '{/x_' . str_random(8) . '?' . ($with_default ? str_random(6) : '') . '}';

        if ($missed > 0) {
            $missed--;
        } else {
            $example .= '/' . str_random(8);
        }

        $optional--;
    }

    return array($route, $example);
}

function simple_bench($nRoutes, $nMatches)
{
    $parser    = new \Pinepain\SimpleRouting\Parser();
    $collector = new \Pinepain\SimpleRouting\RoutesCollector($parser);

    $compiler  = new \Pinepain\SimpleRouting\Compiler();
    $filter = new \Pinepain\SimpleRouting\Filter([new \Pinepain\SimpleRouting\CompilerFilters\Formats(new \Pinepain\SimpleRouting\CompilerFilters\Helpers\FormatsCollection([['default', '[^/]+']]))]);
    $generator = new \Pinepain\SimpleRouting\RulesGenerator($filter, $compiler);

    $routes   = [];
    $examples = [];

    echo "\n-- Simple Router ---\n\n";

    for ($i = 0, $str = 'a'; $i < $nRoutes; $i++, $str++) {
        $route = generateRoute(2);
        list ($route, $example) = $route;
        $routes[] = $route;
        $examples[] = $example;
        $collector->add($route, 'handler' . $i);
        $lastStr = $example;
    }

    $dynamic_routes = $collector->getDynamicRoutes();

    $generated_data = $generator->generate($dynamic_routes);
    $dispatcher     = new \Pinepain\SimpleRouting\Dispatcher($collector->getStaticRoutes(), $generated_data);

    // first route
    $startTime = microtime(true);
    for ($i = 0; $i < $nMatches; $i++) {
        $parameters = $dispatcher->dispatch($examples[0]);
        //dd($parameters, $routes[0], $examples[0]);
    }
    printf("Simple Router router first route: %f\n", microtime(true) - $startTime);

    // midlle route
    $middle = min(count($routes), ceil(count($routes)/2));

    $startTime = microtime(true);
    for ($i = 0; $i < $nMatches; $i++) {
        $parameters = $dispatcher->dispatch($examples[$middle]);
        //dd($parameters, $routes[$middle], $examples[$middle]);
    }
    printf("Simple Router router middle route: %f\n", microtime(true) - $startTime);


    // last route
    $startTime = microtime(true);
    for ($i = 0; $i < $nMatches; $i++) {
        $parameters = $dispatcher->dispatch($lastStr);

    }
    printf("Simple Router last route: %f\n", microtime(true) - $startTime);
    //var_dump($res);

    // unknown route
    $startTime = microtime(true);
    for ($i = 0; $i < $nMatches; $i++) {
        $parameters = $dispatcher->dispatch('/foobar/bar');
    }
    printf("Simple Router router unknown route: %f\n", microtime(true) - $startTime);
    //var_dump($res);

    echo "\n-----\n\n";
}

function fastroutebench($nRoutes, $nMatches)
{
    $options = [];

    $routes   = [];
    $examples = [];
    echo "\n-- Fast Router ---\n\n";

    $router = FastRoute\simpleDispatcher(function ($router) use ($nRoutes, &$lastStr, &$routes, &$examples) {
        for ($i = 0; $i < $nRoutes; $i++) {
            $route = generateRoute(2);
            list ($route, $example) = $route;
            $routes[] = $route;
            $examples[] = $example;
            $router->addRoute('GET', $route, 'handler' . $i);
            $lastStr = $example;
        }
    }, $options);

    // first route
    $startTime = microtime(true);
    for ($i = 0; $i < $nMatches; $i++) {
        $parameters = $router->dispatch('GET', $examples[0]);
        //dd($parameters, $routes[0], $examples[0]);
    }
    printf("FastRoute first route: %f\n", microtime(true) - $startTime);

    $middle = min(count($routes), ceil(count($routes)/2));

    // middle route
    $startTime = microtime(true);
    for ($i = 0; $i < $nMatches; $i++) {
        $parameters = $router->dispatch('GET', $examples[$middle]);
        //dd($parameters, $routes[$middle], $examples[$middle]);
    }
    printf("FastRoute router middle route: %f\n", microtime(true) - $startTime);


    // last route
    $startTime = microtime(true);
    for ($i = 0; $i < $nMatches; $i++) {
        $res = $router->dispatch('GET', $lastStr);
    }
    printf("FastRoute last route: %f\n", microtime(true) - $startTime);
    //var_dump($res);

    // unknown route
    $startTime = microtime(true);
    for ($i = 0; $i < $nMatches; $i++) {
        $res = $router->dispatch('GET', '/foobar/bar');
    }
    printf("FastRoute unknown route: %f\n", microtime(true) - $startTime);
    //var_dump($res);

    echo "\n-----\n\n";
}

function symfonybench($nRoutes, $nMatches)
{
    echo "\n-- Symfony Router ---\n\n";
    $routes = new Symfony\Component\Routing\RouteCollection();

    for ($i = 0, $str = 'a'; $i < $nRoutes; $i++, $str++) {
        $route = new Symfony\Component\Routing\Route('/' . $str . '/{arg}', array('controller' => 'MyController'));
        $routes->add('handler' . $i, $route);
        $lastStr = $str;
    }

    $context = new Symfony\Component\Routing\RequestContext('/a/foo');
    $matcher = new Symfony\Component\Routing\Matcher\UrlMatcher($routes, $context);
    // first route
    $startTime = microtime(true);
    for ($i = 0; $i < $nMatches; $i++) {
        $parameters = $matcher->match('/a/foo');
    }
    printf("Symfony router first route: %f\n", microtime(true) - $startTime);

    $context = new Symfony\Component\Routing\RequestContext('/' . $lastStr . '/foo');
    $matcher = new Symfony\Component\Routing\Matcher\UrlMatcher($routes, $context);
    // last route
    $startTime = microtime(true);
    for ($i = 0; $i < $nMatches; $i++) {
        $parameters = $matcher->match('/' . $lastStr . '/foo');
    }
    printf("Symfony last route: %f\n", microtime(true) - $startTime);
    //var_dump($res);

    // unknown route
    $startTime = microtime(true);
    for ($i = 0; $i < $nMatches; $i++) {
        try {
            $parameters = $matcher->match('/foobar/bar');
        } catch (Exception $e) {
        }
    }
    printf("Symfony router unknown route: %f\n", microtime(true) - $startTime);
    //var_dump($res);

    echo "\n-----\n\n";
}

function aurabench($nRoutes, $nMatches)
{
    echo "---- aura ---- \n";
    // v1
    // $router = new Map(new DefinitionFactory, new RouteFactory);
    // v2
    $router = new Aura\Router\Router(
        new Aura\Router\RouteCollection(new \Aura\Router\RouteFactory),
        new Aura\Router\Generator
    );

    for ($i = 0, $str = 'a'; $i < $nRoutes; $i++, $str++) {
        // v1
        // $router->add('handler' . $i, '/' . $str . '/{:arg}');
        // v2
        $router->add('handler' . $i, '/' . $str . '/{arg}');
        $lastStr = $str;
    }

    // first route
    $startTime = microtime(true);
    for ($i = 0; $i < $nMatches; $i++) {
        $res = $router->match('/a/foo', $_SERVER);
    }
    printf("Aura router first route: %f\n", microtime(true) - $startTime);

    // last route
    $startTime = microtime(true);
    for ($i = 0; $i < $nMatches; $i++) {
        $res = $router->match('/' . $lastStr . '/foo', $_SERVER);
    }
    printf("Aura router last route: %f\n", microtime(true) - $startTime);

    // unknown route
    $startTime = microtime(true);
    for ($i = 0; $i < $nMatches; $i++) {
        $res = $router->match('/foobar/bar', $_SERVER);
    }
    printf("Aura router unknown route: %f\n", microtime(true) - $startTime);
    //var_dump($res);

    echo "\n-----\n\n";
}

$nRoutes  = 100;
$nMatches = 10000;
echo "No of routes is {$nRoutes} and matches {$nMatches} \n\n";
simple_bench($nRoutes, $nMatches);
fastroutebench($nRoutes, $nMatches);
// that stuff is a bit slow, so not necessary to compare at all, but if you want - go on
//symfonybench($nRoutes, $nMatches);
//aurabench($nRoutes, $nMatches);
