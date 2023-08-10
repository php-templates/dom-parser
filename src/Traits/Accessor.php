<?php

namespace PhpDom\Traits;

use PhpDom\QuerySelector as QuerySelectorClass;

trait Accessor
{
    final public function __get($prop)
    {
        $m = 'get'. ucfirst($prop);
        if (method_exists($this, $m)) {
            return $this->$m();
        }
    }
   
    final public function __set($prop, $val)
    {
        $m = 'set'. ucfirst($prop);
        return $this->$m($val);
    }
}