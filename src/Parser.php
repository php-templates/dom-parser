<?php

namespace PhpTemplates\Dom;

use Closure;
use PhpTemplates\InvalidNodeException;
use PhpTemplates\Source;
// todo: validari cu tipete
class Parser
{
    private $dom;
    private $nodeQueue = [];
    
    private $line = 1;
    private $scope = 'text';
    private $buildingNode;
    private $buildingAttribute;
    
    private $options = [
        'repair_html' => true,
    ];

    public function __construct(array $options = [])
    {
        $this->options = array_merge($this->options, $options);
    }
    
    public function parse(/*Source*/ $source)
    {
        $this->dom = new DomNode('#root');
        $this->nodeQueue = [];
        $this->building = (object) [
            'node' => null,
            'attr' => new \stdClass,
            'text' => '',
        ];
        
        $html = (string)$source;
        $chars = array_map('preg_quote', [
            '<',
            '>',
            '=',
            '"',
            '\'',
            //'!',
            //'?',
            //'-',
            //'\\',
        ]);
        $chars = implode('|', array_merge([
            '<[a-zA-Z0-9_\-]+',
            '<\/[a-zA-Z0-9_\-]+>',
            '\/>',
            '<!--',
            '-->',
            '= *"',
            '= *\'',
            '[\s\t ]+',
            '[\n\r]',
        ], $chars));
 
        $tokens = preg_split("/($chars)/ms", $html, -1, PREG_SPLIT_DELIM_CAPTURE);
        foreach ($tokens as $token) {
            $this->add($token);
        }
        return $this->dom;
    }
    
    private function add($token) 
    {
        //!empty($GLOBALS['x']) && var_dump($this->scope.' += '.$token);
        if (preg_match_all('/[\n\r]/', $token, $m)) {
            $this->line += count($m[0]);
        }
        $GLOBALS['x'] = !empty($GLOBALS['x']) || ($this->buildingNode && strpos($this->buildingNode->nodeValue, '22222222'));
        
        $this->{$this->scope . 'Scope'}($token);
    }
    
    private function textScope($token) 
    {
        $parentNode = end($this->nodeQueue);
        $parentNode = $parentNode ? $parentNode : $this->dom;
        if (!$this->buildingNode) {
            $this->buildingNode = new DomNode('#text', '');
        }
        
        if ($token == '<!--') {
            $this->buildingNode->nodeValue .= $token;
            $this->scope = 'comment';
        }
        
        elseif (preg_match('/<\/([a-zA-Z0-9_\-]+)>/', $token, $m)) 
        {
            // <foo>[</foo>]
            if ($parentNode !== $this->dom && $parentNode->nodeName != $m[1]) {
                if ($this->options['repair_html']) {
                    return $this->tryCloseTag($m[1]);
                }
                throw new \Exception("Unexpected token $token at line {$this->line}, expecting end tag for node <{$parentNode->nodeName}> started at line {$parentNode->lineNumber}");
            }
            
            if (trim($this->buildingNode->nodeValue) !== '') {
                $parentNode->appendChild($this->buildingNode);
            }
            array_pop($this->nodeQueue);
            //$m[1]=='script' && d(end($this->nodeQueue));
            $this->buildingNode = null;
        }
        
        elseif (preg_match('/<([a-zA-Z0-9_\-]+)/', $token, $m)) 
        {
            // [<foo] bar=""
            if (trim($this->buildingNode->nodeValue) !== '') {
                $parentNode->appendChild($this->buildingNode);
            }
            
            $this->buildingNode = new DomNode($m[1]);
            $this->buildingNode->lineNumber = $this->line;
            $this->scope = 'nodeDeclaration';
        }
        
        else 
        {
            $this->buildingNode->nodeValue .= $token;
        }        
    }
    
    private function nodeDeclarationScope($token)
    {
        $parentNode = end($this->nodeQueue);
        $parentNode = $parentNode ? $parentNode : $this->dom;

        if ($token == '>') {
            // <foo[>]
            $parentNode->appendChild($this->buildingNode);
            if (!in_array($this->buildingNode->nodeName, DomNode::$selfClosingTags)) {
                $this->nodeQueue[] = $this->buildingNode;
            }
            if ($this->buildingNode->nodeName == 'script') {
                $this->scope = 'script';
            } else {
                $this->scope = 'text';
            }
            $this->buildingNode = new DomNode('#text', '');
        }
        
        elseif ($token == '/>') {
            // <foo [/>]
            $this->buildingNode->shortClose = true;
            $parentNode->appendChild($this->buildingNode);
            $this->buildingNode = new DomNode('#text', '');
            $this->scope = 'text';
        }
        
        elseif (trim($token) !== '') {
            // <foo [bar]="bam">
            $this->buildingAttr = new DomNodeAttr($token, null);
            $this->scope = 'nodeAttributeDeclaration';
        }
    }
    
    private function nodeAttributeDeclarationScope($token)
    {
        $delimiter = substr($token, -1);
        if ($token && $token[0] == '=' && in_array($delimiter, ['"', "'"])) {
            // foo[="]bar"
            $this->buildingAttr->valueDelimiter = $delimiter;
            $this->scope = 'nodeAttributeValueDeclaration';
        }
        elseif($token && $token[0] == "=") {
            // foo=[]123
            $this->buildingAttr->valueDelimiter = '';
            $this->scope = 'nodeAttributeValueDeclaration';
        }
        else {
            // foo[ ]bar - html5
            // TODO: throw error if not space
            $this->buildingNode->addAttribute($this->buildingAttr);
            $this->scope = 'nodeDeclaration';
        }
        
        if (in_array($token, ['>', '/>'])) {
            // <foo bar/> - html5
            $this->nodeDeclarationScope($token);
        }
    }
    
    private function nodeAttributeValueDeclarationScope($token)
    {
        if ($token && $token == $this->buildingAttr->valueDelimiter) {
            $this->scope = 'nodeDeclaration';
            // foo="x["]
            $this->buildingNode->addAttribute($this->buildingAttr);
            $this->buildingAttr = null;
        }
        else {
            // foo="[x][ ][y]"
            $this->buildingAttr->nodeValue .= $token;
        }        
    }
    
    private function commentScope($token)
    {
        $this->buildingNode->nodeValue .= $token;
        if ($token == '-->') {
            $this->scope = 'text';
        }
    }  
    
    private function scriptScope($token) 
    {
        $parentNode = end($this->nodeQueue);
        //$parentNode = $parentNode ? $parentNode : $this->dom;
        if (!$this->buildingNode) {
            $this->buildingNode = new DomNode('#text', '');
        }
        
        if ($token == '</script>') 
        {
            if (trim($this->buildingNode->nodeValue) !== '') {
                $parentNode->appendChild($this->buildingNode);
            }
            array_pop($this->nodeQueue);
            $this->scope = 'text';
            $this->buildingNode = null;
        }
        else {
            $this->buildingNode->nodeValue .= $token;
        }      
    }
    
    private function tryCloseTag($name)
    {
        $parentNode = end($this->nodeQueue);
        $parentNode = $parentNode ? $parentNode : $this->dom;
        
        if ($name == 'br') {
            return $parentNode->appendChild(new DomNode('br'));
        }
        
        $max = count($this->nodeQueue) -1;
        $shouldClose = false;
        for ($i = $max; $i >= 0; $i--) {
            if ($this->nodeQueue[$i]->nodeName == $name) {
                $shouldClose = true;
                break;
            }
        }
        if (!$shouldClose) {
            return;
        }
        
        $node = array_pop($this->nodeQueue);
        while ($node && $node->nodeName != $name) {
            $node = array_pop($this->nodeQueue);
        }
    }
}