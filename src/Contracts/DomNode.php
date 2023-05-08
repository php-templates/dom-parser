<?php

namespace PhpTemplates\Dom\Contracts;

abstract class DomNode
{
    /**
     * DomNodeElement name, like input, textarea, div, etc.
     * Nodes without rendering tags (like textnodes) will start with '#' and they will output only their 'nodeValue'
     *
     * @var string
     */
    public string $nodeName;

    /**
     * Parent node, null if rootnode
     *
     * @var self|null
     */
    public ?self $parentNode;

    /**
     * Prev sibling node or null
     *
     * @var self|null
     */
    public ?self $prevSibling;

    /**
     * Next sibling node or null
     *
     * @var self|null
     */
    public ?self $nextSibling;

    /**
     * ChildNodes linked list
     *
     * @var DomNodeIterator of childNodes
     */
    public DomNodeIterator $childNodes;

    /**
     * @var extra meta data, like start line, file, etc
     */
    public array $data = [];

    /**
     * Node to html code
     *
     * @return string
     */
    abstract public function __toString(): string;

    /**
     * Clone node without references
     *
     * @return self
     */
    // public function cloneNode(): self
    // {dd(4);
    //     $arr = $this->__toArray();
    //     $clone = self::fromArray($arr);
    //     $clone->srcFile = $this->srcFile;
    //     $clone->lineNumber = $this->lineNumber;
    //     return $clone;
    // }

    private function assertNotContained(DomNode $parent, DomNode $append)
    {
        if ($parent === $append) {
            throw new \Exception('Parent Node is contained by appended Node. This will cause recursivity');
        }
        foreach ($append->childNodes as $cn) {
            $this->assertNotContained($parent, $cn);
        }
    }

     /**
     * Append a new child node to current node.
     * If appended node already exists in this node flow, it will throw an error to prevent infinite recursion
     *
     * @param DomNode|string $node - when string, we will call DomNode::fromString to obtain a virtual node
     * @return DomNode
     */
    public function appendChild(DomNode $node): self
    {
        $this->assertNotContained($this, $node);

        if ($this->childNodes) {
            $node->insertAfter($this->childNodes->last());
        }
        else {
            $node->appendTo($this);
        }

        return $this;
    }

    /**
     * Preprend a new child node to current node.
     * If preprended node already exists in this node flow, it will throw an error to prevent infinite recursion
     *
     * @param DomNode|string $node - when string, we will call DomNode::fromString to obtain a virtual node
     * @return DomNode
     */
    public function prependChild(DomNode $node): self
    {
        $this->assertNotContained($this, $node);

        if ($this->childNodes->head) {
            $node->insertBefore($this->childNodes->head);
        }
        else {
            $node->appendTo($this);
        }

        return $this;
    }

    /**
     * Insert a child node before another given childnode
     * If appended node already exists in this node flow, it will throw an error to prevent infinite recursion
     *
     * @param DomNode
     * @return DomNode
     */
    public function insertBefore(DomNode $node): self
    {
        // involved nodes
        $nodeParentNode = $node->parentNode;
        $nodePrevSibling = $node->prevSibling;

        // prepare node
        $this->detach();
        $this->assertNotContained($node, $this);

        // do insert
        $this->parentNode = $node->parentNode;
        $node->prevSibling = $this;
        $this->nextSibling = $node;
        if ($nodePrevSibling) {
            $nodePrevSibling->nextSibling = $this;
        } else {
            $nodeParentNode->childNodes->head = $this;
        }

        return $this;
    }

    /**
     * Insert a node as another given node child.
     * If appended node already exists in this node flow, it will throw an error to prevent infinite recursion
     *
     * @param DomNode
     * @return DomNode
     */
    public function appendTo(DomNode $node): self
    {
        // involved nodes
        $nodeLastSibling = $node->childNodes->last();

        // prepare node
        $this->detach();
        $this->assertNotContained($node, $this);

        // do insert
        $this->parentNode = $node;
        $this->prevSibling = $nodeLastSibling;
        $nodeLastSibling->nextSibling = $this;

        return $this;
    }

    /**
     * Insert a child node after another given childnode
     * If appended node already exists in this node flow, it will throw an error to prevent infinite recursion
     *
     * @param DomNode
     * @return DomNode
     */
    public function insertAfter(DomNode $node): self
    {
        // involved nodes
        $nodeParentNode = $node->parentNode;
        $nodeNextSibling = $node->nextSibling;

        // prepare node
        $this->detach();
        $this->assertNotContained($node, $this);

        // do insert
        $this->parentNode = $node->parentNode;
        $this->prevSibling = $node;
        $node->nextSibling = $this;
        if ($nodeNextSibling) {
            $nodeNextSibling->prevSibling = $this;
        }

        return $this;
    }

    /**
     * Append a new child node before current node.
     * If appended node already exists in this node flow, it will throw an error to prevent infinite recursion
     *
     * @param DomNode|string $node - when string, we will call DomNode::fromString to obtain a virtual node
     * @return DomNode
     */
    public function before(DomNode $node): self
    {
        $this->assertNotContained($this, $node);

        $node->insertBefore($this);

        return $this;
    }

    /**
     * Append a new child node after current node.
     * If appended node already exists in this node flow, it will throw an error to prevent infinite recursion
     *
     * @param DomNode|string $node - when string, we will call DomNode::fromString to obtain a virtual node
     * @return DomNode
     */
    public function after($node): self
    {
        $this->assertNotContained($this, $node);

        $node->insertAfter($this);

        return $this;
    }

    /**
     * Remove all childnode
     *
     * @return DomNode
     */
    //public function empty(): DomNode;

    /**
     * Remove childnode from its parent and returns it available to be attached (insert,append) elsewhere
     *
     * @return DomNode
     */
    public function detach(): self
    {
        $prev = $this->prevSibling;
        $next = $this->nextSibling;
        $prev && $prev->nextSibling = $next;
        $next && $next->prevSibling = $prev;
        $this->parentNode = $this->prevSibling = $this->nextSibling = null;

        return $this;
    }

    // /**
    //  * returns an array representation of dom structure
    //  *
    //  * @return array
    //  */
    // public function debug(): array
    // {
    //     $x = ['tag' => $this->nodeName, 'node_id' => $this->nodeId, 'file' => $this->srcFile, 'line' => $this->lineNumber];
    //     if ($this->nodeName == '#text') {
    //         $x['text'] = $this->nodeValue;
    //     }
    //     foreach ($this->attrs as $a) {
    //         $x['attrs'][$a->nodeName] = $a->nodeValue;
    //     }
    //     foreach ($this->childNodes as $cn) {
    //         $x['childs'][] = $cn->debug();
    //     }
    //     return $x;
    // }

    public function querySelector(string $selector)
    {
        return (new QuerySelector($this))->find($selector, false);
    }

    public function querySelectorAll(string $selector)
    {
        return (new QuerySelector($this))->find($selector);
    }
}