<?php namespace Mongolog\Bundle\MongologBrowserBundle\Services\QueryParser\Compiler;

use Mongolog\Bundle\MongologBrowserBundle\Services\QueryParser\Compiler\Token\LogicalOperator;

class StackLevel implements StackItemInterface {

    /**
     * @var array
     */
    private $items = array();

    /**
     * @var StackLevel
     */
    private $parent;

    /**
     * @var StackExpression
     */
    protected $currentExpression;

    /**
     * @var LogicalOperator
     */
    private $operator;

    /**
     * @param LogicalOperator $operator
     * @param StackLevel $parent
     */
    public function __construct(LogicalOperator $operator, StackLevel $parent = null)
    {
        $this->operator = $operator;

        $this->parent = $parent;

        $this->currentExpression = new StackExpression();
    }

    /**
     * @return $this
     */
    public function endExpression()
    {
        if(!$this->currentExpression->isEmpty())
        {
            $this->items[] = $this->currentExpression;
            $this->currentExpression = new StackExpression();
        }

        return $this;
    }

    /**
     * @param StackItemInterface $item
     * @return $this
     */
    public function add(StackItemInterface $item)
    {
        $this->currentExpression->add($item);

        return $this;
    }

    /**
     * @param LogicalOperator $operator
     * @return static
     */
    public function newLevel(LogicalOperator $operator)
    {
        $new = new static($operator, $this);

        $this->items[] = $new;

        return $new;
    }

    /**
     * @return StackLevel
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * @return array
     */
    public function get()
    {
        return $this->items;
    }

    /**
     * @return LogicalOperator
     */
    public function getOperator()
    {
        return $this->operator;
    }

    /**
     * @return bool
     */
    public function isTopLevel()
    {
        return is_null($this->parent);
    }

    /**
     * @return int
     */
    public function getLevel()
    {
        if(is_null($this->parent))
        {
            return 0;
        }

        return $this->parent->getLevel() + 1;
    }
}