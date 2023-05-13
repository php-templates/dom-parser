<?php

namespace PhpDom\Contracts;

interface DomNodeAttrInterface
{
    /**
     * Attribute to string representation, like href="xyz"
     *
     * @return string
     */
    public function __toString(): string;

    /**
     * Get Atttribute Name
     *
     * @return string
     */
    public function getName(): string;

    /**
     * Get Attribute Value
     *
     * @return mixed
     */
    public function getValue();

    /**
     * Set Attribute Value
     *
     * @param mixed $value
     * @return void
     */
    public function setValue($value): void;

    /**
     * Append Value
     *
     * @param mixed $value
     * @return void
     */
    public function append($value): void;
}