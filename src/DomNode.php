<?php

namespace PhpDom;

use PhpDom\Contracts\DomNodeAttrInterface;
use PhpDom\Contracts\DomNodeInterface;
use PhpDom\Traits\DomElement;
use PhpDom\Traits\QuerySelector;

/**
 * @inheritdoc
 */
class DomNode implements DomNodeInterface
{// todo: validari
    use DomElement;
    use QuerySelector;

    /**
     * DomNodeElement name, like input, textarea, div, etc.
     * Nodes without rendering tags (like textnodes) will start with '#' and they will output only their 'nodeValue'
     *
     * @var string
     */
    protected string $nodeName;

    /**
     * List of node attributes
     *
     * @var array
     */
    protected array $attrs = [];

    /**
     * DomNode Meta Data
     */
    public array $meta = [];


    public function __construct(string $nodeName, array $nodeValue = [])
    {
        $this->nodeName = trim($nodeName);

        // short node declaration syntax
        foreach ($nodeValue as $k => $val) {
            $this->setAttribute($k, $val);
        }
    }

    public function __toString(): string
    {
        $return = '';
        
        // NODE START
        if ($this->nodeName) {
            $attrs = implode(' ', $this->attrs);
            $attrs = $attrs ? ' ' . $attrs : '';
            $return = '<' . $this->nodeName . $attrs . (empty($this->meta['shortClose']) ? '>' : '/>');
        }

        // NODE CONTENT
        foreach ($this->childNodes as $cn) {
            $return .= $cn;
        }

        // NODE END
        if ($this->nodeName && empty($this->meta['shortClose']) && !$this->isSelfClosingTag()) {
            $return .= "</{$this->nodeName}>";
        }

        return $return;
    }

    public function getNodeName(): string
    {
        return $this->nodeName;
    }

    public function setNodeName(string $name): DomNodeInterface
    {
        $this->nodeName = $name;

        return $this;
    }

    public function getAttributes(): array
    {
        return $this->attrs;
    }

    public function getAttribute(string $name)
    {
        foreach ($this->attrs as $attr) {
            if ($attr->getName() == $name) {
                return $attr->getValue();
            }
        }
    }

    public function setAttribute($name, $value = null): DomNodeInterface
    {
        if ($name instanceof DomNodeAttrInterface) {
            /** @var DomNodeAttrInterface */
            $attr = $name;
            $this->attrs[] = $attr;

            return $this;
        }

        foreach ($this->attrs as $attr) {
            if ($attr->getName() == $name) {
                $attr->setValue($value);

                return $this;
            }
        }

        $this->attrs[] = new DomNodeAttr($name, $value);

        return $this;
    }


    public function hasAttribute(string $name): bool
    {
        foreach ($this->attrs as $attr) {
            if ($attr->getName() == $name) {
                return true;
            }
        }
        return false;
    }

    public function removeAttribute(string $name): self
    {
        if ($name == '*') {
            $this->attrs = [];
            return $this;
        }
        
        foreach ($this->attrs as $i => $attr) {
            if ($attr->getName() == $name) {
                unset($this->attrs[$i]);
            }
        }

        return $this;
    }
    
    // todo make this
    public function getFile() 
    {
        return $this->meta['file'] ?? null;
    }
    
    // todo make this
    public function getLine() 
    {
        return $this->meta['line'] ?? null;
    }
    
    

    public static $selfClosingTags = [
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
    private function isSelfClosingTag(): bool
    {
        return in_array($this->nodeName, self::$selfClosingTags);
    }
}
