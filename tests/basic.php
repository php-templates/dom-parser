<?php

require('./../autoload.php');

$parser = new PhpDom\Parser([

]);

$html = file_get_contents('./cases/1.html');
$dom = $parser->parse(new PhpDom\Source($html, 'cases/1.html'));
$result = preg_replace('/[\n\r\t\s]*/', '', $dom);
$expected = preg_replace('/[\n\r\t\s]*/', '', $html);
$_expected = str_split($expected, 400);
$_result = str_split($result, 400);

foreach ($_expected as $i => $_expected) {
    $r = $_result[$i] ?? '';
    if ($_expected == $r) {
        continue;
    }
    echo $_expected;
    echo PHP_EOL;
    echo $r;
    die();
}

echo "PASSED";
