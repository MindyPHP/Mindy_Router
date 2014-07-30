<?php
/**
 * 
 *
 * All rights reserved.
 * 
 * @author Falaleev Maxim
 * @email max@studio107.ru
 * @version 1.0
 * @company Studio107
 * @site http://studio107.ru
 * @date 30/07/14.07.2014 14:23
 */

namespace Mindy\Router;


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

