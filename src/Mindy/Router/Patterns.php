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
use Yii;

class Patterns
{
    public $patterns = [];

    public $namespace = '';

    protected $parentPrefix;

    public function __construct($patterns, $namespace = '')
    {
        if(is_string($patterns)) {
            $tmp = Yii::getPathOfAlias($patterns);
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

    public function setParentPrefix($prefix)
    {
        $this->parentPrefix = $prefix;
        return $this;
    }

    public function getPrefix($prefix)
    {
        return $this->parentPrefix ? $this->parentPrefix . $prefix : $prefix;
    }

    public function parse(array $patterns)
    {
        $className = __CLASS__;
        $routes = [];
        foreach($patterns as $urlPrefix => $params) {
            if($params instanceof $className) {
                /* @var $params Patterns */
                $routes = array_merge($routes, $params->setParentPrefix($urlPrefix)->getRoutes());
            } else {
                if(!array_key_exists('callback', $params)) {
                    continue;
                } else {
                    if($urlPrefix[0] != '/') {
                        $urlPrefix = '/' . $urlPrefix;
                    }

                    $callback = explode(':', $params['callback']);
                    list($controller, $action) = $callback;
                    $callbackParams = [
                        'controller' => $controller,
                        'action' => $action
                    ];

                    if(array_key_exists('values', $params)) {
                        $params['values'] = array_merge($params['values'], $callbackParams);
                    } else {
                        $params['values'] = $callbackParams;
                    }
                    unset($params['callback']);
                }

                $prefix = $this->parentPrefix ? $this->parentPrefix : $urlPrefix;
                if(!array_key_exists($prefix, $routes)) {
                    $routes[$prefix] = [
                        'routes' => []
                    ];
                }

                if(!empty($this->namespace)) {
                    $routes[$prefix]['name_prefix'] = $this->namespace . '.';
                }

                $name = $params['name'];
                unset($params['name']);
                $params['path'] = $prefix == $urlPrefix ? '' : $urlPrefix;
                $routes[$prefix]['routes'][$name] = $params;
            }
        }

        return $routes;
    }

    public function getRoutes()
    {
        return $this->parse($this->patterns);
    }
}
