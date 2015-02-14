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

use Mindy\Router\Patterns;
use TestCase;

class PatternsTest extends TestCase
{
    public function testSimple()
    {
        $patterns = new Patterns([
            '/' => new Patterns([
                '/blog' => [

                ]
            ], 'blog')
        ]);

        $this->assertEquals([], $patterns->getPatterns());
    }
}
