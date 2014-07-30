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

use Exception;
use Mindy\Helper\Alias;

class Patterns
{
    public $patterns = [];

    public $namespace = '';

    protected $parentPrefix;

    protected $trailingSlash = false;

    public function __construct($patterns, $namespace = '')
    {
        if(is_string($patterns)) {
            $tmp = Alias::get($patterns);
            if(!$tmp) {
                $tmp = $patterns;
            } else {
                $tmp .= '.php';
            }

            if(is_file($tmp)) {
                $patterns = require $tmp;
            } else {
                throw new Exception("No such urls file $tmp");
            }

            if(!is_array($patterns)) {
                throw new Exception("Patterns must be a an array or alias to routes file: $patterns");
            }
        }
        $this->patterns = $patterns;
        $this->namespace = $namespace;
    }

    public function getPatterns()
    {
        return $this->patterns;
    }

    public function setTrailingSlash($value)
    {
        $this->trailingSlash = $value;
        return $this;
    }

    public function parse(RouteCollector $collector, array $patterns, $parentPrefix = '')
    {
        foreach($patterns as $urlPrefix => $params) {
            if($params instanceof Patterns || $params instanceof CustomPatterns) {
                /* @var $params Patterns */
                $params->parse($collector, $params->getPatterns(), $urlPrefix);
            } else {
                if(!array_key_exists('callback', $params)) {
                    continue;
                } else {
                    if($urlPrefix[0] != '/') {
                        $urlPrefix = '/' . $urlPrefix;
                    }

                    $callback = explode(':', $params['callback']);
                }

                if(!empty($this->namespace)) {
                    $name = $this->namespace . '.' . $params['name'];
                } else {
                    $name = $params['name'];
                }

                $collector->any([$parentPrefix . $urlPrefix, $name], $callback);
            }
        }
    }

    public function getRouteCollector()
    {
        $collector = new RouteCollector(new RouteParser);
        $this->parse($collector, $this->patterns);
        return $collector;
    }
}
