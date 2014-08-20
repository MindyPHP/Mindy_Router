<?php

namespace Mindy\Router;

use Mindy\Router\Exception\BadRouteException;

/**
 * Parses routes of the following form:
 *
 * "/user/{name}/{id:[0-9]+}"
 */
class RouteParser
{
    /**
     * Parse patterns regex
     */
    const VARIABLE_REGEX =
        "~\{
            \s* ([a-zA-Z][a-zA-Z0-9_]*) \s*
            (?:
                : \s* ([^{}]*(?:\{(?-1)\}[^{}*])*)
            )?
        \}\??~x";
    /**
     * Default regex
     */
    const DEFAULT_DISPATCH_REGEX = '[^/]+';
    /**
     * @var
     */
    private $parts;
    /**
     * @var
     */
    private $reverseParts;
    /**
     * @var
     */
    private $partsCounter;
    /**
     * @var
     */
    private $variables;
    /**
     * @var
     */
    private $regexOffset;
    /**
     * @var array
     */
    private $regexShortcuts = [
        ':i}' => ':[0-9]+}',
        ':a}' => ':[0-9A-Za-z]+}',
        ':h}' => ':[0-9A-Fa-f]+}',
        ':c}' => ':[a-zA-Z0-9+_-\.]+}',
    ];

    /**
     * @param $route
     * @return array
     */
    public function parse($route)
    {
        $this->reset();

        $route = strtr($route, $this->regexShortcuts);

        if (!$matches = $this->extractVariableRouteParts($route)) {
            return [[$this->quote($route)], $route];
        }

        foreach ($matches as $set) {
            $this->staticParts($route, $set[0][1]);
            $this->validateVariable($set[1][0]);
            $regexPart = (isset($set[2]) ? trim($set[2][0]) : self::DEFAULT_DISPATCH_REGEX);
            $this->regexOffset = $set[0][1] + strlen($set[0][0]);
            $match = '(' . $regexPart . ')';
            if (substr($set[0][0], -1) === '?') {
                $match = $this->makeOptional($match);
            }
            $this->reverseParts[$this->partsCounter] = '{' . $set[1][0] . '}';
            $this->parts[$this->partsCounter++] = $match;
        }

        $this->staticParts($route, strlen($route));

        return [[implode('', $this->parts), $this->variables], implode('', $this->reverseParts)];
    }

    /**
     * @void
     */
    private function reset()
    {
        $this->parts = [];

        $this->reverseParts = [];

        $this->partsCounter = 0;

        $this->variables = [];

        $this->regexOffset = 0;
    }

    /**
     * @param $route
     * @return null
     */
    private function extractVariableRouteParts($route)
    {
        if (preg_match_all(self::VARIABLE_REGEX, $route, $matches, PREG_OFFSET_CAPTURE | PREG_SET_ORDER)) {
            return $matches;
        }

        return null;
    }

    /**
     * @param $route
     * @param $nextOffset
     */
    private function staticParts($route, $nextOffset)
    {
        $static = preg_split('~(/)~u', substr($route, $this->regexOffset, $nextOffset - $this->regexOffset), 0, PREG_SPLIT_DELIM_CAPTURE);
        foreach ($static as $staticPart) {
            if ($staticPart) {
                $this->parts[$this->partsCounter] = $staticPart;
                $this->reverseParts[$this->partsCounter] = $staticPart;

                $this->partsCounter++;
            }
        }
    }

    /**
     * @param $varName
     * @throws Exception\BadRouteException
     */
    private function validateVariable($varName)
    {
        if (isset($this->variables[$varName])) {
            throw new BadRouteException("Cannot use the same placeholder '$varName' twice");
        }

        $this->variables[$varName] = $varName;
    }

    /**
     * @param $match
     * @return string
     */
    private function makeOptional($match)
    {
        $previous = $this->partsCounter - 1;

        if (isset($this->parts[$previous]) && $this->parts[$previous] === '/') {
            $this->partsCounter--;
            $match = '(?:/' . $match . ')';
        }

        return $match . '?';
    }

    /**
     * @param $part
     * @return mixed
     */
    private function quote($part)
    {
        return $part;
        //return preg_quote($part, '~');
    }

}
