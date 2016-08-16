<?php

include 'vendor/autoload.php';

use Mindy\Router\Dispatcher;
use Mindy\Router\RouteCollector;
use Mindy\Router\RouteParser;

$collector = new RouteCollector(new RouteParser);

$collector->get('/test', function () {

});

$collector->get('/test2', function () {

});

$collector->get('/test3', function () {

});

$collector->get('/test1/{name}', function () {

});

$collector->get('/test2/{name2}', function () {

});

$collector->get('/test3/{name3}', function () {

});

$dispatcher = new Dispatcher($collector);

$runTime = 10;

$time = microtime(true);

$count = 0;
$seconds = 0;
while ($seconds < $runTime) {
    $count++;
    $dispatcher->dispatch('GET', '/test2/joe');

    if ($time + 1 < microtime(true)) {
        $time = microtime(true);
        $seconds++;
        echo $count . ' routes dispatched per second' . "\r";
        $count = 0;
    }
}

echo PHP_EOL;