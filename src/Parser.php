<?php

namespace PhpDom;

use Closure;
use SplFileInfo;
use PhpDom\Contracts\DomElementInterface;
use PhpDom\Contracts\DomNodeAttrInterface;
use PhpDom\Exceptions\UnclosedTagException;
use PhpDom\Exceptions\InvalidNodeException;

// todo: validari cu tipete
class Parser
{
    private static $htmlTags = [
        'a',
        'abbr',
        'address',
        'area',
        'article',
        'aside',
        'audio',
        'b',
        'base',
        'bdi',
        'bdo',
        'blockquote',
        'body',
        'br',
        'button',
        'canvas',
        'caption',
        'cite',
        'code',
        'col',
        'colgroup',
        'data',
        'datalist',
        'dd',
        'del',
        'details',
        'dfn',
        'dialog',
        'div',
        'dl',
        'dt',
        'em',
        'embed',
        'fieldset',
        'figcaption',
        'figure',
        'footer',
        'form',
        'h1',
        'h2',
        'h3',
        'h4',
        'h5',
        'h6',
        'head',
        'header',
        'hr',
        'html',
        'i',
        'iframe',
        'img',
        'input',
        'ins',
        'kbd',
        'label',
        'legend',
        'li',
        'link',
        'main',
        'map',
        'mark',
        'meta',
        'meter',
        'nav',
        'noscript',
        'object',
        'ol',
        'optgroup',
        'option',
        'output',
        'p',
        'param',
        'picture',
        'pre',
        'progress',
        'q',
        'rb',
        'rp',
        'rt',
        'rtc',
        'ruby',
        's',
        'samp',
        'script',
        'section',
        'select',
        'slot',
        'small',
        'source',
        'span',
        'strong',
        'style',
        'sub',
        'summary',
        'sup',
        'table',
        'tbody',
        'td',
        'template',
        'textarea',
        'tfoot',
        'th',
        'thead',
        'time',
        'title',
        'tr',
        'track',
        'u',
        'ul',
        'var',
        'video',
        'wbr',
    ];
    
    public static $repair_html = false;
    public static $throw_errors = false;
    
    private ?DomNode $dom;
    private array $nodeQueue = [];

    private ?SplFileInfo $file;
    private int $line = 1;
    private string $scope = 'text';
    private ?DomElementInterface $buildingNode;
    private ?DomNodeAttrInterface $buildingAttr;

    private $options = [];

    public function __construct(array $options = [])
    {
        $this->options = array_merge([
            'repair_html' => self::$repair_html,
            'throw_errors' => self::$throw_errors,
        ], $options);
    }

    public function parse(Source $source)
    {
        // reset
        $this->file = new SplFileInfo($source->getFile());
        $this->line = 1;
        $this->dom = new DomNode('');
        $this->nodeQueue = [];
        $this->scope = 'text';
        $this->buildingNode = null;
        $this->buildingAttr = null;

        // parse
        $tokens = $this->tokenize((string)$source);
        foreach ($tokens as $token) {
            $this->add($token);
        }

        if ($this->buildingNode) {
            $this->dom->appendChild($this->buildingNode);
        }
            
        // return nodelist or node (if only one root element found)
        if ($this->dom->getChildNodes()->count() > 1) {
            return $this->dom;
        }

        if (! $this->dom->getChildNodes()->first()) {
            return $this->dom;
        }

        return $this->dom->getChildNodes()->first();
    }
    
    // split html string into relevant tokens to be interpreted in context
    protected function tokenize(string $html)
    {
        static $chars;
        if (!$chars) 
        {
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
        }

        return preg_split("/($chars)/ms", $html, -1, PREG_SPLIT_DELIM_CAPTURE);        
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
            $this->buildingNode->meta['file'] = $this->file;
            $this->buildingNode->meta['line'] = $this->line;
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
                $inFile = $this->file ? 'in ' . $this->file->getRealPath() : '';
                throw new UnclosedTagException("Unexpected token ". htmlentities($token) ." $inFile at line {$this->line}, expecting end tag for node '". $parentNode->getNodeName() ."' started at line {$parentNode->meta['line']}", (string)$this->file, $this->line);
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
            $this->buildingNode->meta['file'] = $this->file;
            $this->buildingNode->meta['line'] = $this->line;
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
            $this->buildingNode->meta['file'] = $this->file;
            $this->buildingNode->meta['line'] = $this->line;
        }

        elseif ($token == '/>') {
            // <foo [/>]
            $this->buildingNode->meta['shortClose'] = true;
            $parentNode->appendChild($this->buildingNode);
            $this->buildingNode = new TextNode();
            $this->buildingNode->meta['file'] = $this->file;
            $this->buildingNode->meta['line'] = $this->line;
            $this->scope = 'text';
        }
        
        elseif (preg_match('/^[\'"<=]/', trim($token))) {
            throw new InvalidNodeException("Unexpected token ". htmlentities($token), (string)$this->file, $this->line);
        }

        elseif (trim($token) !== '') {
            // <foo [bar]="bam">
            $this->buildingAttr = new DomNodeAttr($token, null);
            $this->buildingAttr->meta['file'] = $this->file;
            $this->buildingAttr->meta['line'] = $this->line;
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
            $this->buildingNode->meta['file'] = $this->file;
            $this->buildingNode->meta['line'] = $this->line;
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
            $br = new DomNode('br');
            $br->meta['file'] = $this->file;
            $br->meta['line'] = $this->line;
            return $parentNode->appendChild($br);
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
    
    public static function isHtmlTag($tag)
    {
        return in_array($tag, self::$htmlTags);
    }
}