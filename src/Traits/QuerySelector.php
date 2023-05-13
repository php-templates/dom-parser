<?php

namespace PhpDom\Traits;

use PhpDom\QuerySelector as QuerySelectorClass;

trait QuerySelector
{
    /**
     * non complex css selectors supported
     *
     * @param string $selector
     * @return self|null - returns first childNodes matching the given selector. May return null in case of nothing found
     */
    public function querySelector(string $selector)
    {
        return (new QuerySelectorClass($this))->find($selector, false);
    }

    /**
     * non complex css selectors supported
     *
     * @param string $selector
     * @return array - returns an array of childNodes matching the given selector
     */
    public function querySelectorAll(string $selector)
    {
        return (new QuerySelectorClass($this))->find($selector);
    }
}