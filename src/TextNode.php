<?php

namespace PhpTemplates\Dom;

use PhpTemplates\Dom\Contracts\TextNodeInterface;
use PhpTemplates\Dom\Traits\DomElement;

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

}