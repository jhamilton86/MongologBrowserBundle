<?php namespace Mongolog\Bundle\MongologBrowserBundle\Services\QueryParser\Compiler\Token;

use Mongolog\Bundle\MongologBrowserBundle\Services\QueryParser\Compiler\StackItemInterface;

class Token implements StackItemInterface
{
    protected $value;

    public function __construct($value)
    {
        $this->value = $value;
    }

    public function get()
    {
        return $this->value;
    }

    public function __toString()
    {
        return (string)$this->value;
    }
}