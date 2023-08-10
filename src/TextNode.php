<?php

namespace PhpDom;

use PhpDom\Contracts\TextNodeInterface;
use PhpDom\Traits\DomElement;

/**
 * @inheritdoc
 */
class TextNode implements TextNodeInterface
{
    use DomElement;

    /**
     * TextNode content value
     *
     * @var string
     */
    protected string $nodeValue;

    /**
     * TextNode Meta Data
     *
     * @var array
     */
    public array $meta = [];

    public function __construct(string $nodeValue = '')
    {
        $this->nodeValue = $nodeValue;
    }

    public function __toString(): string
    {
        return $this->nodeValue;
    }

    public function getNodeValue(): string
    {
        return $this->nodeValue;
    }

    public function setNodeValue(string $string): TextNodeInterface
    {
        $this->nodeValue = $string;

        return $this;
    }

    public function append(string $string): TextNodeInterface
    {
        $this->nodeValue .= $string;

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
}