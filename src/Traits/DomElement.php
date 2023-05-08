<?php

namespace PhpTemplates\Dom\Traits;

use PhpTemplates\Dom\Contracts\DomElementInterface;
use PhpTemplates\Dom\DomNodeIterator;

trait DomElement
{
    /**
     * Parent node, null if rootnode
     *
     * @var DomElementInterface|null
     */
    protected ?DomElementInterface $parentNode = null;

    /**
     * Prev sibling node or null
     *
     * @var DomElementInterface|null
     */
    protected ?DomElementInterface $prevSibling = null;

    /**
     * Next sibling node or null
     *
     * @var DomElementInterface|null
     */
    protected ?DomElementInterface $nextSibling = null;

    /**
     * ChildNodes linked list
     *
     * @var DomNodeIterator of childNodes
     */
    protected DomNodeIterator $childNodes;


    private function assertNotContained(DomElementInterface $parent, DomElementInterface $append)
    {
        if ($parent === $append) {
            throw new \Exception('Parent Node is contained by appended Node. This will cause recursivity');
        }

        foreach ($append->getChildNodes() as $cn) {
            $this->assertNotContained($parent, $cn);
        }
    }

    public function getParentNode(): ?DomElementInterface
    {
        return $this->parentNode;
    }

    public function getPrevSibling(): ?DomElementInterface
    {
        return $this->prevSibling;
    }

    public function getNextSibling(): ?DomElementInterface
    {
        return $this->nextSibling;
    }

    public function getChildNodes(): DomNodeIterator
    {
        if (empty($this->childNodes)) {
            $this->childNodes = new DomNodeIterator;
        }

        return $this->childNodes;
    }

    public function setParentNode(DomElementInterface $node = null): ?DomElementInterface
    {
        $this->parentNode = $node;

        return $this;
    }

    public function setPrevSibling(DomElementInterface $node = null): ?DomElementInterface
    {
        $this->prevSibling = $node;

        return $this;
    }

    public function setNextSibling(DomElementInterface $node = null): ?DomElementInterface
    {
        $this->nextSibling = $node;

        return $this;
    }

    public function appendChild(DomElementInterface $node): DomElementInterface
    {
        $this->assertNotContained($this, $node);

        if ($this->getChildNodes()->any()) {
            $node->insertAfter($this->getChildNodes()->last());
        }
        else {
            $node->appendTo($this);
        }

        return $this;
    }

    public function prependChild(DomElementInterface $node): DomElementInterface
    {
        $this->assertNotContained($this, $node);

        if ($this->getChildNodes()->head) {
            $node->insertBefore($this->getChildNodes()->head);
        }
        else {
            $node->appendTo($this);
        }

        return $this;
    }

    public function insertBefore(DomElementInterface $node): DomElementInterface
    {
        // involved nodes
        $nodeParentNode = $node->getParentNode();
        $nodePrevSibling = $node->getPrevSibling();

        // prepare node
        $this->detach();
        $this->assertNotContained($node, $this);

        // do insert
        $this->setParentNode($node->getParentNode());
        $node->setPrevSibling($this);
        $this->setNextSibling($node);
        if ($nodePrevSibling) {
            $nodePrevSibling->setNextSibling($this);
        } else {
            $nodeParentNode->getChildNodes()->head = $this;
        }

        return $this;
    }

    public function appendTo(DomElementInterface $node): DomElementInterface
    {
        // involved nodes
        $nodeLastSibling = $node->getChildNodes()->last();

        // prepare node
        $this->detach();
        $this->assertNotContained($node, $this);

        // do insert
        $this->setParentNode($node);
        $this->setPrevSibling($nodeLastSibling);
        if ($nodeLastSibling) {
            $nodeLastSibling->setNextSibling($this);
        } else {
            $node->getChildNodes()->head = $this;
        }

        return $this;
    }

    public function insertAfter(DomElementInterface $node): DomElementInterface
    {
        // involved nodes
        $nodeParentNode = $node->getParentNode();
        $nodeNextSibling = $node->getNextSibling();

        // prepare node
        $this->detach();
        $this->assertNotContained($node, $this);

        // do insert
        $this->setParentNode($nodeParentNode);
        $this->setPrevSibling($node);
        $node->setNextSibling($this);
        if ($nodeNextSibling) {
            $nodeNextSibling->setPrevSibling($this);
        }

        return $this;
    }

    public function before(DomElementInterface $node): DomElementInterface
    {
        $this->assertNotContained($this, $node);

        $node->insertBefore($this);

        return $this;
    }

    public function after(DomElementInterface $node): DomElementInterface
    {
        $this->assertNotContained($this, $node);

        $node->insertAfter($this);

        return $this;
    }

    public function detach(): DomElementInterface
    {
        $prev = $this->getPrevSibling();
        $next = $this->getNextSibling();
        $prev && $prev->setNextSibling($next);
        $next && $next->setPrevSibling($prev);
        $this->setParentNode(null);
        $this->setPrevSibling(null);
        $this->setNextSibling(null);

        return $this;
    }
}