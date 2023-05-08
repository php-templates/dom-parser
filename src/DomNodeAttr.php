<?php

namespace PhpTemplates\Dom;

class DomNodeAttr
{
    public $nodeName;
    public $nodeValue;
    public $valueDelimiter = '"'; // todo

    public function __construct(string $nodeName, string $nodeValue = null)
    {
        $this->nodeName = $nodeName;
        $this->nodeValue = $nodeValue;
    }

    public function __toString()
    {
        if (!$this->nodeName) {
            return $this->nodeValue;
        } elseif (is_null($this->nodeValue)) {
            return $this->nodeName;
        }
        
        $quot = $this->valueDelimiter;

        return $this->nodeName . '=' . $quot . $this->nodeValue . $quot;
    }
}
