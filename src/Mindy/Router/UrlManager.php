<?php

namespace Mindy\Router;

use Mindy\Base\Mindy;
use Mindy\Helper\Traits\Accessors;
use Mindy\Helper\Traits\Configurator;

class UrlManager extends Dispatcher
{
    use Accessors, Configurator;

    public $urlsAlias = 'App.config.urls';
    public $patterns = null;
    public $trailingSlash = true;

    public function __construct($config = [])
    {
        $this->configure($config);

        $patterns = new Patterns(empty($this->patterns) ? $this->urlsAlias : $this->patterns);
        $patterns->setTrailingSlash($this->trailingSlash);

        parent::__construct($patterns->getRouteCollector());

        $this->init();
    }

    public function init()
    {
    }

    public function addPattern($prefix, Patterns $patterns)
    {
        $patterns->setTrailingSlash($this->trailingSlash);
        $patterns->parse($this->collector, $patterns->getPatterns(), $prefix);
    }

    public function getResponse($handler)
    {
        return $handler;
    }

    /**
     * @param $path
     * @void
     */
    public function trailingSlashCallback($path)
    {
        Mindy::app()->request->redirect($path);
    }

    public function reverse($name, $args = [])
    {
        if (is_array($name)) {
            $args = $name;
            $name = $name[0];
            unset($args[0]);
        }
        return parent::reverse($name, $args);
    }

    /**
     * @param $request \Mindy\Http\Request
     * @return false
     */
    public function parseUrl($request)
    {
        $uri = $request->http->getRequestUri();
        $url = ltrim(strtok($uri, "?"), '/');

        $route = $this->dispatch($request->http->getRequestType(), $url);
        if (!$route && $this->trailingSlash === true && substr($url, -1) !== '/') {
            $newUri = $url . '/' . str_replace($url, '', $uri);
            $route = $this->dispatch($request->http->getRequestType(), $newUri);
            if ($route) {
                $this->trailingSlashCallback($newUri);
            }
        }

        return $route;
    }
}
