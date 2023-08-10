<?php

namespace PhpDom\Exceptions;

class InvalidNodeException extends \Exception 
{
    protected $code = 0;  
    protected $file;
    protected $line;
    
    public function __construct($msg, string $file, int $line)
    {
        parent::__construct($msg);
        $this->file = $file;
        $this->line = $line;
    }
}