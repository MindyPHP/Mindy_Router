<?php

namespace Mindy\Router;

/**
 * Class CustomPatterns
 * @package Mindy\Router
 */
class CustomPatterns
{
    public function __construct($namespace = '')
    {
        $this->namespace = $namespace;
    }

    public function parse(RouteCollector $collector, array $patterns, $parentPrefix = '')
    {
        foreach($this->getPatterns() as $pattern => $route) {
            if(isset($route['name'])) {
                $patternParams = [$pattern, $route['name']];
            } else {
                $patternParams = $pattern;
            }
            if(is_string($route['callback'])) {
                $callback = explode(':', $route['callback']);
            } else {
                $callback = $route['callback'];
            }
            $collector->any($patternParams, $callback);
        }
    }

    public function getPatterns()
    {
        return [];
    }
}

