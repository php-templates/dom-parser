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
    private $buildingAttr;

    private $options = [
        'repair_html' => false,
        'throw_errors' => false,
    ];

    public function __construct(array $options = [])
    {
        $this->options = array_merge($this->options, $options);
    }

    public function parse(/*Source*/ $source)
    {todo source class
        $this->dom = new DomNode('#root');
        $this->nodeQueue = [];

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

        if ($this->dom->getChildNodes()->count() > 1) {
            return $this->dom->getChildNodes();
        }

        return $this->dom->getChildNodes()->first();
    }

    protected function add($token)
    {
        if (preg_match_all('/[\n\r]/', $token, $m)) {
            $this->line += count($m[0]);
        }

        $this->{$this->scope . 'Scope'}($token);
    }

    protected function textScope($token)
    {
        $parentNode = end($this->nodeQueue);
        $parentNode = $parentNode ? $parentNode : $this->dom;
        if (!$this->buildingNode) {
            $this->buildingNode = new TextNode();
        }

        if ($token == '<!--') {
            $this->buildingNode->append($token);
            $this->scope = 'comment';
        }

        elseif (preg_match('/<\/([a-zA-Z0-9_\-]+)>/', $token, $m))
        {
            // <foo>[</foo>]
            if ($parentNode !== $this->dom && $parentNode->getNodeName() != $m[1]) {
                if ($this->options['repair_html']) {
                    return $this->tryCloseTag($m[1]);
                }
                elseif (!$this->options['throw_errors']) {
                    // treat it as text
                    return $this->buildingNode->append($token);
                }
                throw new \Exception("Unexpected token $token at line {$this->line}, expecting end tag for node <{$parentNode->getNodeName()}> started at line {$parentNode->meta['lineNumber']}");
            }

            if (trim($this->buildingNode->getNodeValue()) !== '') {
                $parentNode->appendChild($this->buildingNode);
            }
            array_pop($this->nodeQueue);
            //$m[1]=='script' && d(end($this->nodeQueue));
            $this->buildingNode = null;
        }

        elseif (preg_match('/<([a-zA-Z0-9_\-]+)/', $token, $m))
        {
            // [<foo] bar=""
            if (trim($this->buildingNode->getNodeValue()) !== '') {
                $parentNode->appendChild($this->buildingNode);
            }

            $this->buildingNode = new DomNode($m[1]);
            $this->buildingNode->meta['lineNumber'] = $this->line;
            $this->scope = 'nodeDeclaration';
        }

        else
        {
            $this->buildingNode->append($token);
        }
    }

    protected function nodeDeclarationScope($token)
    {
        $parentNode = end($this->nodeQueue);
        $parentNode = $parentNode ? $parentNode : $this->dom;

        if ($token == '>') {
            // <foo[>]
            $parentNode->appendChild($this->buildingNode);
            if (!in_array($this->buildingNode->getNodeName(), DomNode::$selfClosingTags)) {
                $this->nodeQueue[] = $this->buildingNode;
            }
            if ($this->buildingNode->getNodeName() == 'script') {
                $this->scope = 'script';
            } else {
                $this->scope = 'text';
            }
            $this->buildingNode = new TextNode();
        }

        elseif ($token == '/>') {
            // <foo [/>]
            $this->buildingNode->meta['shortClose'] = true;
            $parentNode->appendChild($this->buildingNode);
            $this->buildingNode = new TextNode();
            $this->scope = 'text';
        }

        elseif (trim($token) !== '') {
            // <foo [bar]="bam">
            $this->buildingAttr = new DomNodeAttr($token, null);
            $this->scope = 'nodeAttributeDeclaration';
        }
    }

    protected function nodeAttributeDeclarationScope($token)
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
            $this->buildingNode->setAttribute($this->buildingAttr);
            $this->scope = 'nodeDeclaration';
        }

        if (in_array($token, ['>', '/>'])) {
            // <foo bar/> - html5
            $this->nodeDeclarationScope($token);
        }
    }

    protected function nodeAttributeValueDeclarationScope($token)
    {
        if ($token && $token == $this->buildingAttr->valueDelimiter) {
            $this->scope = 'nodeDeclaration';
            // foo="x["]
            $this->buildingNode->setAttribute($this->buildingAttr);
            $this->buildingAttr = null;
        }
        else {
            // foo="[x][ ][y]"
            $this->buildingAttr->value .= $token;
        }
    }

    protected function commentScope($token)
    {
        $this->buildingNode->append($token);
        if ($token == '-->') {
            $this->scope = 'text';
        }
    }

    protected function scriptScope($token)
    {
        $parentNode = end($this->nodeQueue);
        //$parentNode = $parentNode ? $parentNode : $this->dom;
        if (!$this->buildingNode) {
            $this->buildingNode = new TextNode();
        }

        if ($token == '</script>')
        {
            if (trim($this->buildingNode->getNodeValue()) !== '') {
                $parentNode->appendChild($this->buildingNode);
            }
            array_pop($this->nodeQueue);
            $this->scope = 'text';
            $this->buildingNode = null;
        }
        else {
            $this->buildingNode->append($token);
        }
    }

    protected function tryCloseTag($name)
    {
        $parentNode = end($this->nodeQueue);
        $parentNode = $parentNode ? $parentNode : $this->dom;

        if ($name == 'br') {
            return $parentNode->appendChild(new DomNode('br'));
        }

        $max = count($this->nodeQueue) -1;
        $shouldClose = false;
        for ($i = $max; $i >= 0; $i--) {
            if ($this->nodeQueue[$i]->getNodeName() == $name) {
                $shouldClose = true;
                break;
            }
        }
        if (!$shouldClose) {
            return;
        }

        $node = array_pop($this->nodeQueue);
        while ($node && $node->getNodeName() != $name) {
            $node = array_pop($this->nodeQueue);
        }
    }
}