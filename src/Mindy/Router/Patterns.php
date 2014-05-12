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
 * @date 12/05/14.05.2014 18:21
 */

namespace Mindy\Router;

class Patterns
{
    public function __construct($alias)
    {
        $this->path = Alias::get($alias);
    }

    public function parse(array $parentRules = [], array $parentNames = [], $parentNamespace='', $prefix = '')
    {
        $className = __CLASS__;

        $rules = $names = [];
        foreach ($this->rawRules as $pattern => $item) {
            if ($item instanceof $className) {
                $patternParsed = $item->parse($rules, $names, $this->namespace, $this->formatPrefix($pattern, $prefix));
                $rules = array_merge($rules, $patternParsed->rules);
                $names = array_merge($names, $patternParsed->names);
                unset($patternParsed);
            } else if (is_array($item)) {
                $pattern = $this->formatPrefix($pattern, $prefix);

                if (isset($parentRules[$pattern]) || array_key_exists($pattern, $parentRules)) {
                    throw new DuplicateUrlException("{$pattern} already registered");
                }

                if(!isset($item[0])) {
                    throw new CallbackNotFoundException();
                }

                $callback = $item[0];

                $rules[$pattern] = new Route($pattern, $item, $callback);

                if (isset($item['name']) || array_key_exists('name', $item)) {
                    $name = $parentNamespace . $this->namespace . $item['name'];
                    if (!$prefix && (isset($parentNames[$name]) || array_key_exists($name, $parentNames))) {
                        throw new DuplicateNameException("{$name} already registered");
                    }

                    // $names[$name] = $this->patternize($pattern);

                    $routeData = $item;
                    unset($routeData[0]);
                    unset($routeData['name']);
                    $names[$name] = new Route($pattern, $routeData);
                }
            }
        }
        gc_collect_cycles();

        $this->names = $names;
        $this->rules = $rules;

        return $this;
    }
}
