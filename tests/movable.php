<?php

require('./../autoload.php');

$files = glob('./movable/*');

foreach ($files as $f) {
    if (!is_file($f)) {
        continue;
    }

    ob_start();
    include($f);
    $r = ob_get_contents();
    ob_end_clean();

    list($result, $expected) = explode('=====', $r);
    $result = preg_replace('/[\n\r\t\s]*/', '', $result);
    $expected = preg_replace('/[\n\r\t\s]*/', '', $expected);

    $tmp = explode('/', $f);
    $tmp = end($tmp);

    if ($expected == $result) {
        print_r($tmp . ' passed' . PHP_EOL);
        continue;
    }

    print_r($tmp . ' failed' . PHP_EOL);
    echo $expected;
    echo PHP_EOL;
    echo $result;
    die();
}

