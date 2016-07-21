<?php

/**
 * All rights reserved.
 *
 * @author Falaleev Maxim
 * @email max@studio107.ru
 * @version 1.0
 * @company Studio107
 * @site http://studio107.ru
 * @date 14/02/15 18:24
 */

namespace Mindy\Router\Dispatcher;

use Mindy\Router\Dispatcher;
use Mindy\Router\Patterns;

class CustomDispatcher extends Dispatcher
{
    public function trailingSlashCallback($uri)
    {
        return 301;
    }
}

class PatternsTest extends \PHPUnit_Framework_TestCase
{
    public function testSimple()
    {
        $callback = function () {
            return true;
        };

        $patterns = new Patterns([
            '/blog' => new Patterns([
                '/' => [
                    'name' => 'index',
                    'callback' => $callback
                ]
            ], 'blog'),
            '' => new Patterns([
                'forum' => [
                    'name' => 'index',
                    'callback' => $callback
                ]
            ], 'forum'),
            '/page' => new Patterns([
                '/' => [
                    'name' => 'index',
                    'callback' => $callback
                ]
            ], 'page')
        ]);
        $c = $patterns->getRouteCollector();

        $this->assertEquals('/blog/', $c->reverse('blog:index'));
        $this->assertEquals('/forum', $c->reverse('forum:index'));
        $this->assertEquals('/page/', $c->reverse('page:index'));

        $d = new CustomDispatcher($c);

        $this->assertNotNull($d->dispatch('GET', '/blog/'));
        $this->assertEquals(301, $d->dispatch('GET', '/blog'));
        $this->assertNotNull($d->dispatch('GET', '/page/'));
        $this->assertEquals(301, $d->dispatch('GET', '/page'));
    }
}
