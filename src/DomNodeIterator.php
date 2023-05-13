<?php

namespace PhpDom;

use PhpDom\Contracts\DomElementInterface;

class DomNodeIterator implements \Iterator, DomElementInterface
{
    public ?DomElementInterface $head = null;
    private ?DomElementInterface $current = null;
    private int $i = 0;

    public function rewind()
    {
        $this->i = 0;
        $this->current = $this->head;
    }

    public function current()
    {
        return $this->current;
    }

    public function key()
    {
        return $this->i;
    }

    public function next()
    {
        $this->current = $this->current->getNextSibling();
        $this->i++;
    }

    public function valid()
    {
        return !is_null($this->current);
    }

    public function any(): bool
    {
        return !is_null($this->head);
    }

    public function first(): ?DomElementInterface
    {
        return $this->head;
    }

    public function item(int $index): ?DomElementInterface
    {
        if (!$this->head) {
            return null;
        }

        $item = $this->head;
        for ($i = 0; $i < $index; $i++) {
            $item = $item->getNextSibling();
        }

        return $item;
    }

    public function last(): ?DomElementInterface
    {
        if (!$this->head) {
            return null;
        }

        $last = $this->head;
        while ($next = $last->getNextSibling()) {
            $last = $next;
        }

        return $last;
    }

    public function count(): int
    {
        if (!$this->head) {
            return 0;
        }

        $i = 1;
        $last = $this->head;
        while ($next = $last->getNextSibling()) {
            $last = $next;
            $i++;
        }

        return $i;
    }

    public function __toString(): string
    {
        if (!$this->head) {
            return '';
        }

        $output = (string)$this->head;
        $last = $this->head;
        while ($next = $last->getNextSibling()) {
            $last = $next;
            $output .= $next;
        }

        return $output;
    }

    public function getParentNode(): ?DomElementInterface
    {
        if ($this->head) {
            return $this->head->getParentNode();
        }
    }

    public function getPrevSibling(): ?DomElementInterface
    {
        if ($this->head) {
            return $this->head->getPrevSibling();
        }
    }

    public function getNextSibling(): ?DomElementInterface
    {
        if ($this->head) {
            return $this->head->getNextSibling();
        }
    }

    public function getChildNodes(): DomNodeIterator
    {
        if ($this->head) {
            return $this->head->getChildNodes();
        }

        return new DomNodeIterator;
    }

    public function setParentNode(DomElementInterface $node = null): DomElementInterface
    {
        $this->rewind();
        while ($elNode = $this->current) {
            $this->next();
            $elNode->setParentNode($node);
        }

        return $this;
    }

    public function setPrevSibling(DomElementInterface $node = null): DomElementInterface
    {
        $this->rewind();
        while ($elNode = $this->current) {
            $this->next();
            $elNode->setPrevSibling($node);
        }

        return $this;
    }

    public function setNextSibling(DomElementInterface $node = null): DomElementInterface
    {
        $this->rewind();
        while ($elNode = $this->current) {
            $this->next();
            $elNode->setNextSibling($node);
        }

        return $this;
    }

    public function appendChild(DomElementInterface $node): DomElementInterface
    {
        $this->rewind();
        while ($elNode = $this->current) {
            $this->next();
            $elNode->appendChild($node);
        }

        return $this;
    }

    public function prependChild(DomElementInterface $node): DomElementInterface
    {
        $this->rewind();
        while ($elNode = $this->current) {
            $this->next();
            $elNode->prependChild($node);
        }

        return $this;
    }

    public function insertBefore(DomElementInterface $node): DomElementInterface
    {
        $this->rewind();
        while ($elNode = $this->current) {
            $this->next();
            $elNode->insertBefore($node);
        }

        return $this;
    }

    public function appendTo(DomElementInterface $node): DomElementInterface
    {
        $this->rewind();
        while ($elNode = $this->current) {
            $this->next();
            $elNode->appendTo($node);
        }

        return $this;
    }

    public function insertAfter(DomElementInterface $node): DomElementInterface
    {
        $this->rewind();
        while ($elNode = $this->current) {
            $this->next();
            $elNode->insertAfter($node);
        }

        return $this;
    }

    public function before(DomElementInterface $node): DomElementInterface
    {
        $this->rewind();
        while ($elNode = $this->current) {
            $this->next();
            $elNode->before($node);
        }

        return $this;
    }

    public function after(DomElementInterface $node): DomElementInterface
    {
        $this->rewind();
        while ($elNode = $this->current) {
            $this->next();
            $elNode->after($node);
        }

        return $this;
    }

    public function detach(): DomElementInterface
    {
        $this->rewind();
        while ($elNode = $this->current) {
            $this->next();
            $elNode->detach();
        }

        return $this;
    }
}