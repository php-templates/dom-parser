<?php

error_reporting(E_ALL);
error_reporting(-1);
ini_set('error_reporting', E_ALL);

 if (!function_exists('dd')) {
    function dd(...$data) {
        $x = debug_backtrace();
        print_r($x[0]['file'].':'.$x[0]['line'].PHP_EOL);
        foreach ($data as $d) {
            //var_dump($d);
            echo '<pre>';
            print_r($d);
            echo '</pre>';
        }
        exit();
    }
}

function d(...$data) {return;
    $GLOBALS['X'] = $GLOBALS['X'] ?? 0;
    $GLOBALS['X']++;
    $GLOBALS['X'] > 100 && die();
    //$x = debug_backtrace();
    //print_r($x[0]['file'].':'.$x[0]['line'].PHP_EOL);
    foreach ($data as $d) {
        //var_dump($d);
        //echo '<pre>';
        print_r($d);
        //echo '</pre>';
        echo PHP_EOL;
    }
}

spl_autoload_register(function ($class) {
    $class = trim($class, '\\');
    $path = 'PhpTemplates\\Dom';
    if (strpos($class, $path) === 0) {
        $class = str_replace($path, '', $class);
        $file = __DIR__.'/src/'.$class.'.php';
        $file = str_replace('\\', '/', $file);
        if (file_exists($file)) {
            require_once($file);
        }
    }
});