<?php

require('./../autoload.php');

use PhpTemplates\Dom\Parser;
use PhpTemplates\Dom\DomNode;
use PhpTemplates\Dom\QuerySelector;

$parser = new PhpTemplates\Dom\Parser([

]);

$html = file_get_contents('./cases/2.html');
$dom = $parser->parse($html);

d(''.$dom->querySelector('el1'));
d('-----');
d(''.$dom->querySelector('el1 + el2'));
d('-----');
d(''.$dom->querySelector('el1 ~ el3'));
d('-----');
d(''.$dom->querySelector('el1 * el3'));
d('-----');
d(''.$dom->querySelector('el1 ~ el3 > el4.ce-class el5'));
d('-----');
d(''.$dom->querySelector('el2[foo^="bar"][bar="y"] + el3 > el4.ce-class el5'));
d('-----');
d(''.$dom->querySelector('el2[foo$="at"][bar="y"] + el3 > el4.ce-class el5'));
d('----');
d(''.$dom->querySelector('el2[foo$="at"][bar="y"] + el3 > el4.ce-class el5.c1.c2'));
d('----');
d('done');