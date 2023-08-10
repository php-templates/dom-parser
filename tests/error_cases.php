<?php

require('./../autoload.php');

$parser = new PhpDom\Parser([
    'throw_errors' => true
]);

$files = scandir('./error_cases');
$files = array_diff($files, ['.', '..', './']);

foreach($files as $f) {
    if (isset($_GET['t']) && explode('.', $f)[0] !== $_GET['t']) {
        continue;
    }
    $file = './error_cases/'.$f;
    $content = file_get_contents($file);
    
    list($html, $expected) = explode('=====', $content);

    try {
        $parser->parse(new PhpDom\Source($html, $file));
        echo $file . ' failed because error expected'; die();
    } catch(Exception $e) {
        [$message, $f, $line] = explode('/', trim($expected));
        if (stripos($e->getMessage(), $message) === false || $e->getLine() != $line || stripos($e->getFile(), $f) === false) {
            dd($file, [$message, $f, $line], $e);
        }
    }
}
echo 'done';        