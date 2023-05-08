<?php

use PhpTemplates\Dom\DomNode;

$r = new DomNode('root');
$n1 = new DomNode('n1');
$n2 = new DomNode('n2');
$n3 = new DomNode('n3');

$r->appendChild($n1);
$r->appendChild($n2);
$r->appendChild($n3);

try {
    $r->insertBefore($n2);
} catch (Exception $e) {
    echo $e->getMessage();
}

try {
    $r->insertBefore($r);
} catch (Exception $e) {
    echo $e->getMessage();
}

try {
    $n2->before($r);
} catch (Exception $e) {
    echo $e->getMessage();
}

?>
=====
Parent Node is contained by appended Node. This will cause recursivity
Parent Node is contained by appended Node. This will cause recursivity
Parent Node is contained by appended Node. This will cause recursivity