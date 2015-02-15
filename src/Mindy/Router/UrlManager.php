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
     * @DEPRECATED
     * @param $request \Mindy\Http\Request
     * @return false
     */
    public function parseUrl($request)
    {
        return $this->dispatch($request->http->getRequestType(), $request->http->getRequestUri());
    }
}
