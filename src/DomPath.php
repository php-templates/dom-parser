<?php

namespace PhpTemplates\Dom;

use PhpTemplates\Dom\Contracts\DomElementInterface;
use PhpTemplates\Dom\Contracts\DomNodeInterface;
use Closure;

class DomPath
{
    private static $map;
    
    private string $token;
    private Closure $selector;
    private ?DomPath $parent;
    private array $params;

    public function __construct(string $selector, DomPath $parent = null)
    {
        $this->boot();
        
        $this->parent = $parent;
        $this->token = $selector;
        foreach (self::$map as $regexp => $cb) {
            if (preg_match("/$regexp/", $selector, $m)) {
                $this->selector = Closure::bind($cb, $this);
                $this->params = $m;
                return;
            }
        }
        
        throw new \Exception("Invalid selector '$selector'");
    }

    public function match(DomElementInterface $node)
    {
        if (! $node instanceof DomNodeInterface) {
            return false;
        }
        
        $match = ($this->selector)($node, $this->params);
        
        return $match;
    }

    private function boot()
    {
        if (self::$map) {
            return;
        }

        $tokens['^$'] = function(DomElementInterface $node) {
            return $this->parent->match($node);
        };

        $tokens['>'] = function(DomElementInterface $node) {
            if ($node = $node->getParentNode()) {
                return $this->parent->match($node);
            }

            return false;
        };

        $tokens['\+'] = function(DomElementInterface $node) {
            if ($node = $node->getPrevSibling()) {
                return $this->parent->match($node);
            }

            return false;
        };

        $tokens['\~'] = function(DomElementInterface $node) {
            while ($node = $node->getPrevSibling()) {
                if ($this->parent->match($node)) {
                    return true;
                }
            }

            return false;
        };

        $tokens[' '] = function(DomElementInterface $node) {
            while ($node = $node->getParentNode()) {
                if ($this->parent->match($node)) {
                    return true;
                }
            }

            return false;
        };

        $tokens['\*'] = function(DomElementInterface $node, $m) {
            // node any
            return true;
        };

        $tokens['^([a-zA-Z0-9:\-_]+)$'] = function(DomElementInterface $node, $m) {
            // node name
            $match = $node->getNodeName() == $m[0];
            if ($match && $this->parent) {
                $match = $this->parent->match($node);
            }
            
            return $match;
        };

        $tokens['^\.([a-zA-Z0-9:\-_]+)$'] = function(DomElementInterface $node, $m) {
            // node class
            $match = false;
            if ($class = (string)$node->getAttribute('class')) {
                $match = in_array($m[1], explode(' ', $class));
            }
            if ($match && $this->parent) {
                $match = $this->parent->match($node);
            }
            
            return $match;
        };

        $tokens['^\#([a-zA-Z0-9:\-_]+)$'] = function(DomElementInterface $node, $m) {
            // node id
            $match = $node->getAttribute('id') == $m[1];
            if ($match && $this->parent) {
                $match = $this->parent->match($node);
            } 
            
            return $match;
        };

        $tokens['^\[([a-zA-Z0-9:\-_]+)=[\'"](.*)[\'"]\]$'] = function(DomElementInterface $node, $m) {
            // node attr
            $match = $node->getAttribute($m[1]) == $m[2];
            if ($match && $this->parent) {
                $match = $this->parent->match($node);
            } 
            
            return $match;
        };

        $tokens['^\[([a-zA-Z0-9:\-_]+)\^=[\'"](.*)[\'"]\]$'] = function(DomElementInterface $node, $m) {
            // node attr STARTS with string
            $match = false;
            if ($attr = (string)$node->getAttribute($m[1])) {
                if ($m[2] == 'class') {
                    $attr = explode(' ', $attr);
                }

                foreach ((array)$attr as $attr) {
                    if (strpos($attr, $m[2]) === 0) {
                        $match = true;
                        break;
                    }
                }
            }

            if ($match && $this->parent) {
                $match = $this->parent->match($node);
            } 
            
            return $match;
        };

        $tokens['^\[([a-zA-Z0-9:\-_]+)\$=[\'"](.*)[\'"]\]$'] = function(DomElementInterface $node, $m) {
            // node attr ENDS with string
            $match = false;
            if ($attr = (string)$node->getAttribute($m[1])) {
                if ($m[2] == 'class') {
                    $attr = explode(' ', $attr);
                }

                foreach ((array)$attr as $attr) {
                    if (substr_compare($attr, $m[2], -strlen($m[2])) === 0) {
                        $match = true;
                        break;
                    }
                }
            }

            if ($match && $this->parent) {
                $match = $this->parent->match($node);
            } 
            
            return $match;
        };

        $tokens['^\[([a-zA-Z0-9:\-_]+)\~=[\'"](.*)[\'"]\]$'] = function(DomElementInterface $node, $m) {
            // node attr CONTAINS string
            $match = false;
            if ($attr = (string)$node->getAttribute($m[1])) {
                $match = strpos($attr, $m[2]) !== false;
            }
            
            if ($match && $this->parent) {
                $match = $this->parent->match($node);
            } 
            
            return $match;
        };

        self::$map = $tokens;
    }
}