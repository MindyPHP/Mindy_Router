<?php
use Mindy\Router\Route;

/**
 * 
 *
 * All rights reserved.
 * 
 * @author Falaleev Maxim
 * @email max@studio107.ru
 * @version 1.0
 * @company Studio107
 * @site http://studio107.ru
 * @date 12/05/14.05.2014 18:36
 */

class RouteTest extends TestCase
{
    public function testInit()
    {
        $route = new Route([
            'callback' => function() {
                return 123;
            }
        ]);
    }
}

