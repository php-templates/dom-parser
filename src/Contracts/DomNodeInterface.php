<?php

namespace PhpTemplates\Dom\Contracts;

interface DomNodeInterface extends DomElementInterface
{
    /**
     * Get Node Name
     *
     * @return string
     */
    public function getNodeName(): string;

    /**
     * Change Node Name to be rendered
     *
     * @return DomNodeInterface
     */
    public function setNodeName(string $name): DomNodeInterface;

    /**
     * Get node attribute value by attribute name, null if no attribute found
     *
     * @param string $name
     * @return mixed
     */
    public function getAttribute(string $name);

    /**
     * Add an attribute to node
     *
     * @param string|DomNodeAttrInterface $name
     * @param string|null $value
     * @return DomNode
     */
     public function setAttribute($name, $value = null): DomNodeInterface;

    /**
     * Determine if an attribute exists on current node, by its name
     *
     * @param string $name
     * @return boolean
     */
    public function hasAttribute(string $name): bool;

    /**
     * Remove node attribute, return node instance
     *
     * @param string $name
     * @return DomNode
     */
     public function removeAttribute(string $name): DomNodeInterface;
}