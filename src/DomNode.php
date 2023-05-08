<?php

namespace PhpTemplates\Dom;

use PhpTemplates\Dom\Contracts\DomNode as IDomNode;
use PhpTemplates\Dom\Contracts\DomNodeWithAttributes;

class DomNode extends IDomNode implements DomNodeWithAttributes
{
    /**
     * List of node attributes
     *
     * @var array
     */
    protected array $attrs = [];

    /**
     * Used for rendering, it says if parsed syntax was like: <div/>, or in case of false: <div></div>
     *
     * @var boolean
     */
    public bool $shortClose = false;


    public function __construct(string $nodeName, array $nodeValue = [])
    {
        $this->nodeName = trim($nodeName);

        // short node declaration syntax
        foreach ($nodeValue as $k => $val) {
            $this->addAttribute($k, $val);
        }
    }

    /**
     * Node to html code
     *
     * @return string
     */
    public function __toString(): string
    {
        // NODE START
        $attrs = (string)$this->attrs;
        $attrs = $attrs ? ' ' . $attrs : '';
        $return = '<' . $this->nodeName . $attrs . ($this->shortClose ? '/>' : '>');

        // NODE CONTENT
        foreach ($this->childNodes as $cn) {
            $return .= $cn;
        }

        // NODE END
        if (!$this->shortClose && !$this->isSelfClosingTag()) {
            $return .= "</{$this->nodeName}>";
        }

        return $return;
    }
    
    /**
     * Get array of DomNodeAttr items
     * @return array
     */
    public function getAttributes(): array
    {
        return $this->attrs;
    }

    /**
     * Get node attribute value by attribute name, null if no attribute found
     *
     * @param string $name
     * @return mixed
     */
    public function getAttribute(string $name)
    {
        foreach ($this->attrs as $attr) {
            if ($attr->nodeName == $name) {
                return $attr->nodeValue;
            }
        }
    }

    /**
     * Add an attribute to node. If an already existing attribute will be found by given name, its value will be overriden
     *
     * @param string $nodeName
     * @param string $nodeValue
     * @return DomNode
     */
     public function setAttribute(string|IDomNodeAttr $name, $value = true): self
     {
         foreach ($this->attrs as $attr) {
             if ($attr->nodeName == $name) {
                 $attr->nodeValue = $value;

                 return $this;
             }
         }

         $this->attrs[] = new DomNodeAttr($name, $value);

         return $this;
     }

    /**
     * Add an attribute to node
     *
     * @param string|DomNodeAttr $nodeName
     * @param string $nodeValue
     * @return DomNode
     */
    public function addAttribute(string $name, $value = true): self
    {
        $this->attrs[] = new DomNodeAttr($name, $value);

        return $this;
    }

    /**
     * Determine if an attribute exists on current node, by its name
     *
     * @param string $name
     * @return boolean
     */
    public function hasAttribute(string $name): bool
    {
        foreach ($this->attrs as $attr) {
            if ($attr->nodeName == $name) {
                return true;
            }
        }
        return false;
    }

    /**
     * Remove node attribute, return node instance
     *
     * @param string $name
     * @return DomNode
     */
     public function removeAttribute(string $name): self
     {
         foreach ($this->attrs as $i => $attr) {
             if ($attr->nodeName == $name) {
                 unset($this->attrs[$i]);
             }
         }

         return $this;
     }

    /**
     * Determine if is self closing tag
     *
     * @return boolean
     */
    private function isSelfClosingTag(): bool
    {
        static $selfClosingTags;
        if (!$selfClosingTags) {
            $selfClosingTags = [
                'area',
                'base',
                'br',
                'col',
                'embed',
                'hr',
                'img',
                'input',
                'link',
                'meta',
                'param',
                'source',
                'track',
                'wbr',
                'command',
                'keygen',
                'menuitem',
            ];
        }

        return in_array($this->nodeName, $selfClosingTags);
    }
}