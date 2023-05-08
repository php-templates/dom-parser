<?php

namespace PhpTemplates\Dom;

use PhpTemplates\Dom\Contracts\DomElementInterface;

class DomNodeIterator implements \Iterator
{
    public ?DomElementInterface $head = null;
    private ?DomElementInterface $current = null;
    private int $i = 0;

    public function rewind()
    {
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
        for ($i = 1; $i <= $index; $i++) {
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
}