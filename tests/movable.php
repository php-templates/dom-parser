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
    
    if ($expected == $result) {
        $tmp = explode('/', $f);
        print_r(end($tmp) . ' passed');
        continue;
    }
    
    echo $expected;
    echo PHP_EOL;
    echo $result;
    die();
}

