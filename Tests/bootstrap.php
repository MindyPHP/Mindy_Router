<?php

require __DIR__ . '/TestCase.php';
$srcList = require __DIR__ . '/../src.php';
foreach ($srcList as $src) {
    require __DIR__ . '/../src/Mindy/Router/' . $src;
}
