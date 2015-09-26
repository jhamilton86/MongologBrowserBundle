<?php namespace Mongolog\Bundle\MongologBrowserBundle\Services\QueryParser\Compiler;

class StackExpression implements StackItemInterface {

    private $items = array();

    public function add(StackItemInterface $item)
    {
        $this->items[] = $item;
    }

    public function isEmpty()
    {
        return $this->count() === 0;
    }

    public function count()
    {
        return count($this->items);
    }

    public function get()
    {
        return $this->items;
    }

    public function asString()
    {
        return implode(' ', $this->items);
    }
}