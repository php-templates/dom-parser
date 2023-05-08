<?php

namespace PhpTemplates\Dom;

use PhpTemplates\Dom\Contracts\DomNodeAttrInterface;
use PhpTemplates\Dom\Contracts\DomNodeInterface;
use PhpTemplates\Dom\Traits\DomElement;

/**
 * @inheritdoc
 */
class DomNode implements DomNodeInterface
{
    use DomElement;

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
     *
     * @var array
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
        // NODE START
        $attrs = implode(' ', $this->attrs);
        $attrs = $attrs ? ' ' . $attrs : '';
        $return = '<' . $this->nodeName . $attrs . (empty($this->meta['shortClose']) ? '>' : '/>');

        // NODE CONTENT
        foreach ($this->childNodes as $cn) {
            $return .= $cn;
        }

        // NODE END
        if (empty($this->meta['shortClose']) && !$this->isSelfClosingTag()) {
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
            if ($attr->name == $name) {
                return $attr->value;
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
            if ($attr->name == $name) {
                $attr->value = $value;

                return $this;
            }
        }

        $this->attrs[] = new DomNodeAttr($name, $value);

        return $this;
    }


    public function hasAttribute(string $name): bool
    {
        foreach ($this->attrs as $attr) {
            if ($attr->name == $name) {
                return true;
            }
        }
        return false;
    }

    public function removeAttribute(string $name): self
    {
        foreach ($this->attrs as $i => $attr) {
            if ($attr->name == $name) {
                unset($this->attrs[$i]);
            }
        }

        return $this;
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
