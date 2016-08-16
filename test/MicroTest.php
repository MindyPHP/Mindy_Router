<?php
/**
 * Created by PhpStorm.
 * User: max
 * Date: 21/07/16
 * Time: 13:18
 */

namespace Mindy\Router\Tests;

use Mindy\Router\Dispatcher;
use Mindy\Router\RouteCollector;
use Mindy\Router\RouteParser;

class MicroTest extends \PHPUnit_Framework_TestCase
{
    private function dispatch($router, $method, $uri)
    {
        return (new Dispatcher($router))->dispatch($method, $uri);
    }

    public function testMicro()
    {
        $c = new RouteCollector(new RouteParser);
        $c->addRoute(Dispatcher::GET, ['/', 'app:index'], function () {
            return '123';
        });

        $response = $this->dispatch($c, Dispatcher::GET, '/');
        $this->assertEquals('123', $response);

        $this->assertEquals('/', $c->reverse('app:index'));
    }
}