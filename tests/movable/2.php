<?php

use PhpTemplates\Dom\DomNode;

$r = new DomNode('root');
$n1 = new DomNode('n1');
$n2 = new DomNode('n2');
$n3 = new DomNode('n3');

$r->appendChild($n1);
$r->appendChild($n2);
$r->appendChild($n3);

$n2->insertBefore($n1);

echo $r;
?>
=====
<root>
    <n2></n2>
    <n1></n1>
    <n3></n3>
</root>