<?php

namespace Mindy\Router;

abstract class BaseLangUrlManager extends UrlManager
{
    public $langInQuery = true;

    public function reverse($name, $args = [])
    {
        if ($this->langInQuery) {
            $url = parent::reverse($name, $args);
            if (strpos($url, '?') === false) {
                $url .= '?lang=' . $this->lang;
            } else {
                $url .= '&lang=' . $this->lang;
            }
            return $url;
        } else {
            if (strpos($name, 'admin') === false) {
                $args = array_merge(['lang' => $this->lang], $args);
            }
            return parent::reverse($name, $args);
        }
    }

    /**
     * @return string
     */
    abstract public function getLang();
}
