<?php

namespace Mindy\Router;

use Mindy\Router\Exception\BadRouteException;
use ReflectionClass;
use ReflectionMethod;

class RouteCollector
{

    const DEFAULT_CONTROLLER_ROUTE = 'index';

    const APPROX_CHUNK_SIZE = 10;

    private $routeParser;
    private $staticRoutes = [];
    private $regexToRoutesMap = [];
    private $reverse = [];

    public function __construct(RouteParser $routeParser = null)
    {
        $this->routeParser = $routeParser ? : new RouteParser();
    }

    public function route($name, $args = [])
    {
        $replacements = (array)$args;
        if(count($replacements)) {
            return preg_replace(array_fill(0, count($replacements), '/\{[^\{\}\/]+\}/'), $replacements, $this->reverse[$name], 1)
        } else {
            return $this->reverse[$name];
        }
    }

    public function addRoute($httpMethod, $route, $handler)
    {
        if (is_array($route)) {
            list($route, $name) = $route;
        }

        list($routeData, $reverseData) = $this->routeParser->parse(trim($route, '/'));

        if (isset($name)) {
            $this->reverse[$name] = $reverseData;
        }

        if (isset($routeData[1])) {
            $this->addVariableRoute($httpMethod, $routeData, $handler);
        } else {
            $this->addStaticRoute($httpMethod, $routeData, $handler);
        }

        return $this;
    }

    private function addStaticRoute($httpMethod, $routeData, $handler)
    {
        $routeStr = $routeData[0];

        if (isset($this->staticRoutes[$routeStr][$httpMethod])) {
            throw new BadRouteException("Cannot register two routes matching '$routeStr' for method '$httpMethod'");
        }

        foreach ($this->regexToRoutesMap as $regex => $routes) {
            if (isset($routes[$httpMethod]) && preg_match('~^' . $regex . '$~', $routeStr)) {
                throw new BadRouteException("Static route '$routeStr' is shadowed by previously defined variable route '$regex' for method '$httpMethod'");
            }
        }

        $this->staticRoutes[$routeStr][$httpMethod] = array($handler, []);
    }

    private function addVariableRoute($httpMethod, $routeData, $handler)
    {
        list($regex, $variables) = $routeData;

        if (isset($this->regexToRoutesMap[$regex][$httpMethod])) {
            throw new BadRouteException("Cannot register two routes matching '$regex' for method '$httpMethod'");
        }

        $this->regexToRoutesMap[$regex][$httpMethod] = array($handler, $variables);
    }

    public function get($route, $handler)
    {
        return $this->addRoute(Route::GET, $route, $handler);
    }

    public function head($route, $handler)
    {
        return $this->addRoute(Route::HEAD, $route, $handler);
    }

    public function post($route, $handler)
    {
        return $this->addRoute(Route::POST, $route, $handler);
    }

    public function put($route, $handler)
    {
        return $this->addRoute(Route::PUT, $route, $handler);
    }

    public function delete($route, $handler)
    {
        return $this->addRoute(Route::DELETE, $route, $handler);
    }

    public function options($route, $handler)
    {
        return $this->addRoute(Route::OPTIONS, $route, $handler);
    }

    public function any($route, $handler)
    {
        return $this->addRoute(Route::ANY, $route, $handler);
    }

    public function controller($route, $classname)
    {
        $reflection = new ReflectionClass($classname);

        $validMethods = $this->getValidMethods();

        foreach ($reflection->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
            foreach ($validMethods as $valid) {
                if (stripos($method->name, $valid) === 0) {
                    $methodName = $this->camelCaseToDashed(substr($method->name, strlen($valid)));

                    $params = $this->buildControllerParameters($method);

                    if ($methodName === self::DEFAULT_CONTROLLER_ROUTE) {
                        $this->addRoute($valid, $route . $params, array($classname, $method->name));
                    }

                    $sep = $route === '/' ? '' : '/';

                    $this->addRoute($valid, $route . $sep . $methodName . $params, array($classname, $method->name));

                    break;
                }
            }
        }

        return $this;
    }

    private function buildControllerParameters(ReflectionMethod $method)
    {
        $params = '';

        foreach ($method->getParameters() as $param) {
            $params .= "/{" . $param->getName() . "}" . ($param->isOptional() ? '?' : '');
        }

        return $params;
    }

    private function camelCaseToDashed($string)
    {
        return strtolower(preg_replace('/([A-Z])/', '-$1', lcfirst($string)));
    }

    public function getValidMethods()
    {
        return [Route::ANY, Route::GET, Route::POST, Route::PUT, Route::DELETE, Route::HEAD, Route::OPTIONS];
    }

    public function getData()
    {
        if (empty($this->regexToRoutesMap)) {
            return [$this->staticRoutes, []];
        }

        return [$this->staticRoutes, $this->generateVariableRouteData()];
    }

    private function generateVariableRouteData()
    {
        $chunkSize = $this->computeChunkSize(count($this->regexToRoutesMap));
        $chunks = array_chunk($this->regexToRoutesMap, $chunkSize, true);
        return array_map(array($this, 'processChunk'), $chunks);
    }

    private function computeChunkSize($count)
    {
        $numParts = max(1, round($count / self::APPROX_CHUNK_SIZE));
        return ceil($count / $numParts);
    }

    private function processChunk($regexToRoutesMap)
    {
        $routeMap = [];
        $regexes = [];
        $numGroups = 0;
        foreach ($regexToRoutesMap as $regex => $routes) {
            $firstRoute = reset($routes);
            $numVariables = count($firstRoute[1]);
            $numGroups = max($numGroups, $numVariables);

            $regexes[] = $regex . str_repeat('()', $numGroups - $numVariables);

            foreach ($routes as $httpMethod => $route) {
                $routeMap[$numGroups + 1][$httpMethod] = $route;
            }

            $numGroups++;
        }

        $regex = '~^(?|' . implode('|', $regexes) . ')$~';
        return ['regex' => $regex, 'routeMap' => $routeMap];
    }
}
