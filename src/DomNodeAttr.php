<?php

namespace PhpDom;

use PhpDom\Contracts\DomNodeAttrInterface;

class DomNodeAttr implements DomNodeAttrInterface
{
    public $name;
    public $value;
    public $valueDelimiter = '"'; // todo

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

    public function getValue()
    {
        return $this->value;
    }

    public function setValue($value): void
    {
        $this->value = $value;
    }

    public function append($value): void
    {
        $this->value .= $value;
    }
}
