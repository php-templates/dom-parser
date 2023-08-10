<?php

namespace PhpDom;

use PhpDom\Contracts\DomNodeAttrInterface;
use PhpDom\Traits\Accessor;

class DomNodeAttr implements DomNodeAttrInterface
{
    use Accessor;
    
    protected $name;
    protected $value;
    protected $valueDelimiter = '"'; // todo
    
    /**
     * DomNode Meta Data
     */
    public array $meta = [];

    public function __construct(string $name, string $value = null)
    {
        $this->name = $name;
        $this->value = $value;
    }

    public function __toString(): string
    {
        if (!$this->name) {
            return $this->value;
        } elseif (is_null($this->value)) {
            return $this->name;
        }

        $quot = $this->valueDelimiter;

        return $this->name . '=' . $quot . $this->value . $quot;
    }

    public function getName(): string
    {
        return $this->name;
    }
    
    public function getValueDelimiter(): string
    {
        return $this->valueDelimiter;
    }

    public function getValue()
    {
        return $this->value;
    }

    public function setValue($value): void
    {
        $this->value = $value;
    }
    
    public function setValueDelimiter(string $char) 
    {
        $this->valueDelimiter = $char;
    }

    public function append($value): void
    {
        $this->value .= $value;
    }
}
