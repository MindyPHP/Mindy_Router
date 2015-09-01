<?php

namespace Mindy\Router;

use Closure;
use Exception;
use Mindy\Helper\Alias;

/**
 * Class Patterns
 * @package Mindy\Router
 */
class Patterns
{
    /**
     * @var array
     */
    public $patterns = [];
    /**
     * @var string
     */
    public $namespace = '';
    /**
     * @var string
     */
    protected $parentPrefix;
    /**
     * @var string
     */
    private $namespaceDelimeter = ':';

    /**
     * @param $patterns
     * @param string $namespace
     * @throws Exception
     */
    public function __construct($patterns, $namespace = '')
    {
        if (is_string($patterns)) {
            $tmp = Alias::get($patterns);
            if (!$tmp) {
                $tmp = $patterns;
            } else {
                $tmp .= '.php';
            }

            if (is_file($tmp)) {
                $patterns = require $tmp;
            } else {
                throw new Exception("No such urls file $tmp");
            }

            if (!is_array($patterns)) {
                throw new Exception("Patterns must be a an array or alias to routes file: $patterns");
            }
        }
        $this->patterns = $patterns;
        $this->namespace = $namespace;
    }

    /**
     * @return array
     */
    public function getPatterns()
    {
        return $this->patterns;
    }

    /**
     * @param RouteCollector $collector
     * @param array $patterns
     * @param string $parentPrefix
     * @throws Exception
     */
    public function parse(RouteCollector $collector, array $patterns, $parentPrefix = '')
    {
        foreach ($patterns as $urlPrefix => $params) {
            if ($params instanceof Patterns || $params instanceof CustomPatterns) {
                $params->parse($collector, $params->getPatterns(), $urlPrefix);
            } else {
                if (!array_key_exists('callback', $params)) {
                    continue;
                }

                if ($params['callback'] instanceof Closure) {
                    $callback = $params['callback'];
                } else if (strpos($params['callback'], ':') !== false) {
                    $callback = explode(':', $params['callback']);
                } else {
                    throw new Exception("Incorrect callback in rule " . $params['name']);
                }

                if (!empty($this->namespace)) {
                    $name = $this->namespace . $this->namespaceDelimeter . $params['name'];
                } else {
                    $name = $params['name'];
                }

                $method = Route::ANY;
                if (isset($params['method']) && in_array(strtoupper($params['method']), $collector->getValidMethods())) {
                    $method = strtoupper($params['method']);
                }

                $collector->$method([trim($parentPrefix, '/') . $urlPrefix, $name], $callback);
            }
        }
    }

    /**
     * @return RouteCollector
     */
    public function getRouteCollector()
    {
        $collector = new RouteCollector(new RouteParser);
        $this->parse($collector, $this->patterns);
        return $collector;
    }
}
