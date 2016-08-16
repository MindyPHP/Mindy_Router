<?php

namespace Mindy\Router;

use Mindy\Router\Exception\HttpMethodNotAllowedException;

/**
 * Class Dispatcher
 * @package Mindy\Router
 */
class Dispatcher
{
    const ANY = 'ANY';
    const GET = 'GET';
    const HEAD = 'HEAD';
    const POST = 'POST';
    const PUT = 'PUT';
    const DELETE = 'DELETE';
    const OPTIONS = 'OPTIONS';
    
    /**
     * @var RouteCollector
     */
    public $collector;
    /**
     * @var
     */
    public $matchedRoute;
    /**
     * @var
     */
    private $staticRouteMap;
    /**
     * @var
     */
    private $variableRouteData;
    /**
     * @var bool
     */
    public $trailingSlash = true;

    /**
     * Dispatcher constructor.
     * @param RouteCollector $collector
     */
    public function __construct(RouteCollector $collector)
    {
        $this->collector = $collector;
        list($this->staticRouteMap, $this->variableRouteData) = $collector->getData();
    }

    /**
     * @param $name
     * @param array $args
     * @return string
     */
    public function reverse($name, $args = [])
    {
        return $this->collector->reverse($name, $args);
    }

    /**
     * @param $httpMethod
     * @param $uri
     * @return bool|mixed
     */
    public function dispatch($httpMethod, $uri)
    {
        $cleanUri = ltrim(strtok($uri, '?'), '/');
        $data = $this->dispatchRoute($httpMethod, $cleanUri);
        if ($data === false) {
            if ($this->trailingSlash && substr($cleanUri, -1) !== '/') {
                $data = $this->dispatchRoute($httpMethod, $cleanUri . '/');
                if ($data === false) {
                    return false;
                } else {
                    $query = ltrim(str_replace($cleanUri, '', $uri), '/');
                    $this->trailingSlashCallback('/' . $cleanUri . '/' . $query);
                }
            } else {
                return false;
            }
        }

        return $this->getResponse($data);
    }

    /**
     * Redirect to new url
     * @param $uri
     */
    public function trailingSlashCallback($uri)
    {
        header("HTTP/1.1 301 Moved Permanently");
        header("Location: " . $uri);
        die();
    }

    /**
     * @param $data
     * @return mixed
     */
    public function getResponse($data)
    {
        list($handler, $vars) = $data;
        return call_user_func_array($this->resolveHandler($handler), $vars);
    }

    /**
     * @param $handler
     * @return array
     */
    public function resolveHandler($handler)
    {
        if (is_array($handler) and is_string($handler[0])) {
            $handler[0] = new $handler[0];
        }

        return $handler;
    }

    /**
     * @param $httpMethod
     * @param $uri
     * @return bool
     */
    public function dispatchRoute($httpMethod, $uri)
    {
        if (isset($this->staticRouteMap[$uri])) {
            return $this->dispatchStaticRoute($httpMethod, $uri);
        }

        return $this->dispatchVariableRoute($httpMethod, $uri);
    }

    /**
     * @param $httpMethod
     * @param $uri
     * @return mixed
     * @throws HttpMethodNotAllowedException
     */
    private function dispatchStaticRoute($httpMethod, $uri)
    {
        $routes = $this->staticRouteMap[$uri];

        if (!isset($routes[$httpMethod])) {
            $httpMethod = $this->checkFallbacks($routes, $httpMethod);
        }

        return $routes[$httpMethod];
    }

    /**
     * @param $routes
     * @param $httpMethod
     * @return mixed
     * @throws HttpMethodNotAllowedException
     */
    private function checkFallbacks($routes, $httpMethod)
    {
        $additional = [self::ANY];

        if ($httpMethod === self::HEAD) {
            $additional[] = self::GET;
        }

        foreach ($additional as $method) {
            if (isset($routes[$method])) {
                return $method;
            }
        }

        $this->matchedRoute = $routes;

        throw new HttpMethodNotAllowedException('Allow: ' . implode(', ', array_keys($routes)));
    }

    /**
     * @param $httpMethod
     * @param $uri
     * @return bool
     * @throws HttpMethodNotAllowedException
     */
    private function dispatchVariableRoute($httpMethod, $uri)
    {
        foreach ($this->variableRouteData as $data) {
            if (!preg_match($data['regex'], $uri, $matches)) {
                continue;
            }

            $count = count($matches);

            while (!isset($data['routeMap'][$count++]));

            $routes = $data['routeMap'][$count - 1];

            if (!isset($routes[$httpMethod])) {
                $httpMethod = $this->checkFallbacks($routes, $httpMethod);
            }

            foreach (array_values($routes[$httpMethod][1]) as $i => $varName) {
                // if (!isset($matches[$i + 1]) || $matches[$i + 1] === '') {
                if (!isset($matches[$i + 1])) {
                    unset($routes[$httpMethod][1][$varName]);
                } else {
                    $routes[$httpMethod][1][$varName] = $matches[$i + 1];
                }
            }

            return $routes[$httpMethod];
        }

        // throw new HttpRouteNotFoundException('Route ' . $uri . ' does not exist');
        return false;
    }

}
