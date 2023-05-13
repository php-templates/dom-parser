<?php

namespace PhpDom;

use PhpDom\Contracts\DomNodeInterface;
use PhpDom\Contracts\DomElementInterface;
use Closure;

/*
.class	.intro	Selects all elements with class="intro"
.class1.class2	.name1.name2	Selects all elements with both name1 and name2 set within its class attribute
.class1 .class2	.name1 .name2	Selects all elements with name2 that is a descendant of an element with name1
#id	#firstname	Selects the element with id="firstname"
*	*	Selects all elements
element	p	Selects all <p> elements
element.class	p.intro	Selects all <p> elements with class="intro"
element element	div p	Selects all <p> elements inside <div> elements
element>element	div > p	Selects all <p> elements where the parent is a <div> element
element+element	div + p	Selects the first <p> element that is placed immediately after <div> elements
element1~element2	p ~ ul	Selects every <ul> element that is preceded by a <p> element
[attribute]	[target]	Selects all elements with a target attribute
*/

class QuerySelector
{
    private DomNodeInterface $node;

    public function __construct(DomNodeInterface $node)
    {
        $this->node = $node;
    }

    public function find(string $selector, bool $many = true)
    {
        $tokens = $this->tokenize($selector);

        $path = null;
        foreach ($tokens as $token) {
            $path = new DomPath($token, $path);
        }
    
        $list = new DomNodeList();
        $this->nodeWalkRecursive($this->node, function($node) use ($path, $list, $many) {
            if ($path->match($node)) {
                $list->push($node);
                // break recursivity at first find, if not asked for multiple find
                return $many;
            }
            
            return true;
        });
        
        if ($many) {
            return $list;
        }
        
        return $list->first();
    }

    private function tokenize(string $selectors): array
    {
        // sanitize selector
        $selectors = trim($selectors);
        $selectors = preg_replace('/[\s\t\r\n ]+/', ' ', $selectors);
        $selectors = preg_replace('/ *([>:\+\~]) */', '$1', $selectors);
        $parts = preg_split("/([:\[\]>\~\+\.#\* ])/ms", $selectors, -1, PREG_SPLIT_DELIM_CAPTURE);
        $parts = array_filter($parts);
     
        $tokens = [];
        $push = '';
        foreach ($parts as $part) {
            if (in_array($part, ['.', '#', '['])) {
                $push = $part;
            }
            elseif (in_array($push, ['.', '#']))
            {
                $tokens[] = $push . $part;
                $push = '';
            }
            elseif ($push && $part != ']') {
                $push .= $part;
            }
            else {
                $tokens[] = $push . $part;
                $push = '';
            }
        }
        
        return $tokens;
    }

    private function nodeWalkRecursive(DomElementInterface $node, Closure $cb)
    {
        $break = !$cb($node);
        if ($break) {
            return false;
        }
        
        foreach ($node->getChildNodes() as $node) {
            if (! $this->nodeWalkRecursive($node, $cb)) {
                break;
            }
        }
        
        return true;
    }
}