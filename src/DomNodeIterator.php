<?php

namespace PhpTemplates\Dom;

class DomNodeIterator implements \Iterator
{
    public ?DomNode $head;
    private ?DomNode $current;
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
        $this->current = $this->current->nextSibling;
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

    public function last()
    {
        $last = $this->node;
        while ($next = $last->nextSibling) {
            $last = $next;
        }

        return $last;
    }
}