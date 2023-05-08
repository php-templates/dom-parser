<?php

namespace PhpTemplates\Dom\Contracts;

use PhpTemplates\Dom\DomNodeIterator;

interface DomElementInterface
{
    /**
     * Node To HTML code
     *
     * @return string
     */
    public function __toString(): string;

    public function getParentNode(): ?DomElementInterface;

    public function getPrevSibling(): ?DomElementInterface;

    public function getNextSibling(): ?DomElementInterface;

    public function getChildNodes(): DomNodeIterator;

    public function setParentNode(DomElementInterface $node = null): DomElementInterface;

    public function setPrevSibling(DomElementInterface $node = null): DomElementInterface;

    public function setNextSibling(DomElementInterface $node = null): DomElementInterface;

    /**
     * Append a new child node to current node.
     * If appended node already exists in this node flow, it will throw an error to prevent infinite recursion
     *
     * @param DomElementInterface
     * @return DomElementInterface
     */
    public function appendChild(DomElementInterface $node): DomElementInterface;

    /**
     * Preprend a new child node to current node.
     * If preprended node already exists in this node flow, it will throw an error to prevent infinite recursion
     *
     * @param DomElementInterface
     * @return DomElementInterface
     */
    public function prependChild(DomElementInterface $node): DomElementInterface;


    /**
     * Insert a child node before another given childnode
     * If appended node already exists in this node flow, it will throw an error to prevent infinite recursion
     *
     * @param DomElementInterface
     * @return DomElementInterface
     */
    public function insertBefore(DomElementInterface $node): DomElementInterface;

    /**
     * Insert a node as another given node child.
     * If appended node already exists in this node flow, it will throw an error to prevent infinite recursion
     *
     * @param DomElementInterface
     * @return DomElementInterface
     */
    public function appendTo(DomElementInterface $node): DomElementInterface;

    /**
     * Insert a child node after another given childnode
     * If appended node already exists in this node flow, it will throw an error to prevent infinite recursion
     *
     * @param DomElementInterface
     * @return DomElementInterface
     */
    public function insertAfter(DomElementInterface $node): DomElementInterface;

    /**
     * Append a new child node before current node.
     * If appended node already exists in this node flow, it will throw an error to prevent infinite recursion
     *
     * @param DomElementInterface
     * @return DomElementInterface
     */
    public function before(DomElementInterface $node): DomElementInterface;

    /**
     * Append a new child node after current node.
     * If appended node already exists in this node flow, it will throw an error to prevent infinite recursion
     *
     * @param DomElementInterface|string $node - when string, we will call DomElementInterface::fromString to obtain a virtual node
     * @return DomElementInterface
     */
    public function after(DomElementInterface $node): DomElementInterface;

    /**
     * Remove childnode from its parent and returns it available to be attached (insert,append) elsewhere
     *
     * @return DomElementInterface
     */
    public function detach(): DomElementInterface;
}
