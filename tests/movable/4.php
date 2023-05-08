<?php

use PhpTemplates\Dom\DomNode;

$r = new DomNode('root');
$n1 = new DomNode('n1');
$n2 = new DomNode('n2');
$n3 = new DomNode('n3');

$r->appendChild($n1);
$r->appendChild($n2);
$r->appendChild($n3);

$r2 = new DomNode('root2');
$r2->appendChild($r->getChildNodes());

echo $r;
echo $r2;
?>
=====
<root></root>
<root2>
    <n1></n1>
    <n2></n2>
    <n3></n3>
</root2>