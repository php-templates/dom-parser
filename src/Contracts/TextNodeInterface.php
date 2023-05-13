<?php

namespace PhpDom\Contracts;

interface TextNodeInterface extends DomElementInterface
{
    /**
     * Get TextNode content Value
     *
     * @return string
     */
    public function getNodeValue(): string;

    /**
     * Set TextNode Content Value
     *
     * @param string $string
     * @return TextNodeInterface
     */
    public function setNodeValue(string $string): TextNodeInterface;

    /**
     * Append Value to TextNode Content Value
     *
     * @param string $string
     * @return TextNodeInterface
     */
    public function append(string $string): TextNodeInterface;
}