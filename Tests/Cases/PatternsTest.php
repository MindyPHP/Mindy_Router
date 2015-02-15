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
use TestCase;

class CustomDispatcher extends Dispatcher
{
    public function trailingSlashCallback($uri)
    {
        return 301;
    }
}

class PatternsTest extends TestCase
{
    public function testSimple()
    {
        $callback = function() {
            return true;
        };

        $patterns = new Patterns([
            '/' => new Patterns([
                '/blog/' => [
                    'name' => 'index',
                    'callback' => $callback
                ]
            ], 'blog'),
            '' => new Patterns([
                'forum' => [
                    'name' => 'index',
                    'callback' => $callback
                ]
            ], 'forum')
        ]);
        $c = $patterns->getRouteCollector();

        $this->assertEquals('/blog/', $c->reverse('blog:index'));
        $this->assertEquals('/forum', $c->reverse('forum:index'));

        $d = new CustomDispatcher($c);

        $this->assertNotNull($d->dispatch('GET', '/blog/'));
        $this->assertEquals(301, $d->dispatch('GET', '/blog'));
    }
}
