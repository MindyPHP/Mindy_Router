<?php
use Aura\Router\DefinitionFactory;
use Aura\Router\Map;
use Aura\Router\RouteFactory;
use Mindy\Router\Patterns;
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

class PatternsTest extends TestCase
{
    protected function getInstance(Patterns $patterns)
    {
        // create the route factory
        $route_factory = new RouteFactory;

        // create the definition factory
        $definition_factory = new DefinitionFactory;

        // create a router map with attached route groups
        return new Map($definition_factory, $route_factory, $patterns->getRoutes());
    }

    public function testInit()
    {
        $patterns = new Patterns([
            '/' => [
                'name' => 'home',
                'callback' => 'Home:index'
            ],

            '/blog' => new Patterns([
                '/read/{:id}' => [
                    'name' => 'read',
                    'callback' => 'Blog:read'
                ]
            ], 'blog'),

            '/user' => new Patterns([
                '/login' => [
                    'name' => 'login',
                    'callback' => 'User:login'
                ],
                '/logout' => [
                    'name' => 'logout',
                    'callback' => 'User:logout'
                ],
                '/view/{:slug}' => [
                    'name' => 'view',
                    'callback' => 'User:view'
                ],
            ], 'user')
        ]);

        $dispatcher = $this->getInstance($patterns);

        $this->assertEquals('/', $dispatcher->generate('home'));

        $this->assertEquals('/blog/read/1', $dispatcher->generate('blog.read', ['id' => 1]));

        $this->assertEquals('/user/login', $dispatcher->generate('user.login'));
        $this->assertEquals('/user/logout', $dispatcher->generate('user.logout'));
        $this->assertEquals('/user/view/admin', $dispatcher->generate('user.view', ['slug' => 'admin']));

        $route = $dispatcher->match('/user/view/admin', []);
        $this->assertEquals([
            'controller' => 'User',
            'action' => 'view',
            'slug' => 'admin',
        ], $route->values);
    }
}

