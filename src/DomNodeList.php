<?php

namespace PhpTemplates\Dom;

use PhpTemplates\Dom\Contracts\DomElementInterface;

class DomNodeList implements \Iterator, DomElementInterface
{
    private int $i = 0;
    private array $list = [];

    public function rewind()
    {
        $this->i = 0;
    }

    public function current()
    {
        return $this->list[$this->i];
    }

    public function key()
    {
        return $this->i;
    }

    public function next()
    {
        $this->i++;
    }

    public function valid()
    {
        return isset($this->list[$this->i]);
    }
    
    public function push(DomElementInterface $node)
    {
        $this->list[] = $node;
        
        return $this;
    }

    public function first(): ?DomElementInterface
    {
        if (!$this->list) {
            return null;
        }
        
        return reset($this->list);
    }

    public function item(int $index): ?DomElementInterface
    {
        return $this->list[$index] ?? null;
    }

    public function last(): ?DomElementInterface
    {
        if (!$this->list) {
            return null;
        }    
        
        return end($this->list);
    }

    public function count(): int
    {
        return count($this->list);
    }

    public function __toString(): string
    {
        $output = '';
        foreach ($this->list as $node) {
            $output .= $node;
        }

        return $output;
    }

    public function getParentNode(): ?DomElementInterface
    {
        if (isset($this->list[0])) {
            return $this->list[0]->getParentNode();
        }
    }

    public function getPrevSibling(): ?DomElementInterface
    {
        if (isset($this->list[0])) {
            return $this->list[0]->getPrevSibling();
        }
    }

    public function getNextSibling(): ?DomElementInterface
    {
        if (isset($this->list[0])) {
            return $this->list[0]->getNextSibling();
        }
    }

    public function getChildNodes(): DomNodeIterator
    {
        if (isset($this->list[0])) {
            return $this->list[0]->getChildNodes();
        }

        return new DomNodeIterator;
    }

    public function setParentNode(DomElementInterface $node = null): DomElementInterface
    {
        foreach ($this->list as $elNode) {
            $elNode->setParentNode($node);
        }

        return $this;
    }

    public function setPrevSibling(DomElementInterface $node = null): DomElementInterface
    {
        foreach ($this->list as $elNode) {
            $elNode->setPrevSibling($node);
        }

        return $this;
    }

    public function setNextSibling(DomElementInterface $node = null): DomElementInterface
    {
        foreach ($this->list as $elNode) {
            $elNode->setNextSibling($node);
        }

        return $this;
    }

    public function appendChild(DomElementInterface $node): DomElementInterface
    {
        foreach ($this->list as $elNode) {
            $elNode->appendChild($node);
        }

        return $this;
    }

    public function prependChild(DomElementInterface $node): DomElementInterface
    {
        foreach ($this->list as $elNode) {
            $elNode->prependChild($node);
        }

        return $this;
    }

    public function insertBefore(DomElementInterface $node): DomElementInterface
    {
        foreach ($this->list as $elNode) {
            $elNode->insertBefore($node);
        }

        return $this;
    }

    public function appendTo(DomElementInterface $node): DomElementInterface
    {
        foreach ($this->list as $elNode) {
            $elNode->appendTo($node);
        }

        return $this;
    }

    public function insertAfter(DomElementInterface $node): DomElementInterface
    {
        foreach ($this->list as $elNode) {
            $elNode->insertAfter($node);
        }

        return $this;
    }

    public function before(DomElementInterface $node): DomElementInterface
    {
        foreach ($this->list as $elNode) {
            $elNode->before($node);
        }

        return $this;
    }

    public function after(DomElementInterface $node): DomElementInterface
    {
        foreach ($this->list as $elNode) {
            $elNode->after($node);
        }

        return $this;
    }

    public function detach(): DomElementInterface
    {
        foreach ($this->list as $elNode) {
            $elNode->detach();
        }

        return $this;
    }
}