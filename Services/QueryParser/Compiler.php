<?php namespace Mongolog\Bundle\MongologBrowserBundle\Services\QueryParser;

use Mongolog\Bundle\MongologBrowserBundle\Services\QueryParser\Compiler\Token\LogicalOperator;
use Mongolog\Bundle\MongologBrowserBundle\Services\QueryParser\Compiler\Token\Operator;
use Mongolog\Bundle\MongologBrowserBundle\Services\QueryParser\Compiler\StackExpression;
use Mongolog\Bundle\MongologBrowserBundle\Services\QueryParser\Compiler\StackLevel;
use Mongolog\Bundle\MongologBrowserBundle\Services\QueryParser\Compiler\Token\Literal;
use Mongolog\Bundle\MongologBrowserBundle\Services\QueryParser\Exception\InvalidExpressionException;
use Mongolog\Bundle\MongologBrowserBundle\Services\QueryParser\Exception\QueryParserException;
use Mongolog\Bundle\MongologBrowserBundle\Services\QueryParser\Exception\UnbalancedParenthesisException;
use Mongolog\Bundle\MongologBrowserBundle\Services\QueryParser\Exception\UnexpectedClosingParenthesisException;

class Compiler
{
    /**
     * @param array $tokens
     * @return array
     */
    public function compile(array $tokens)
    {
        $stack = new StackLevel(new LogicalOperator('$and'));

        foreach($tokens as $token)
        {
            switch($token[0]) {
                case Parser::TOK_WHITESPACE:
                    break;

                case Parser::TOK_EXPRESSION_SPLIT:
                    $stack->endExpression();
                    break;

                case Parser::TOK_INT_LITERAL:
                    $stack->add(new Literal((int)$this->retrieveCapture($token)));
                    break;

                case Parser::TOK_FLOAT_LITERAL:
                    $stack->add(new Literal((float)$this->retrieveCapture($token)));
                    break;

                case Parser::TOK_LITERAL:
                case Parser::TOK_STRING_LITERAL:
                    $stack->add(new Literal($this->retrieveCapture($token)));
                    break;

                case Parser::TOK_RBRACKET:
                    if(!$stack = $stack->endExpression()->getParent()) {
                        throw new UnexpectedClosingParenthesisException();
                    }
                    break;

                default:
                    if($token[0] instanceof Operator) {
                        $stack->add($token[0]);
                    }elseif($token[0] instanceof LogicalOperator) {
                        $stack = $stack->endExpression()->newLevel($token[0]);
                    }else{
                        throw new QueryParserException("Unexpected token of type: " . gettype($token[0]));
                    }
            }
        }

        $stack->endExpression();

        if(!$stack->isTopLevel())
        {
            throw new UnbalancedParenthesisException($stack->getLevel());
        }

        $item = $this->groupOperators($stack);

        return $item;
    }

    /**
     * @param StackLevel $stack
     * @return array
     */
    private function groupOperators(StackLevel $stack)
    {
        $group = array();

        foreach($stack->get() as $item)
        {
            if($item instanceof StackLevel) {

//                $op = $item->getOperator()->get();
//
//                $group[$op] = array_merge_recursive(isset($group[$op]) ? $group[$op] : array(), $this->groupOperators($item));

                $group[] = $this->groupOperators($item);

            }elseif($item instanceof StackExpression) {
                $group[] = $this->arrangeSet($item);
            }else{
                throw new QueryParserException("Unexpected value in item of type: " . gettype($item));
            }
        }

        return [$stack->getOperator()->get() => $group];
    }

    /**
     * Here we convert the flat expression array into a keyed array arranged as a mongo query
     *
     * @param StackExpression $set
     * @return array
     */
    private function arrangeSet(StackExpression $set)
    {
        $this->validateSet($set);

        $arr = $set->get();

        return array($arr[0]->get() => array($arr[1]->get() => $arr[2]->get()));
    }

    /**
     * @param StackExpression $set
     */
    private function validateSet(StackExpression $set)
    {
        // Set should contain 3 items: "column_name   operator   value", EG. "level > 200"
        if($set->count() !== 3)
        {
            throw new InvalidExpressionException($set->asString());
        }

        $arr = $set->get();

        if(!$arr[0] instanceof Literal || !$arr[1] instanceof Operator || !$arr[2] instanceof Literal){
            throw new InvalidExpressionException($set->asString());
        }
    }

    /**
     * @param $parts
     * @return mixed
     */
    protected function retrieveCapture($parts)
    {
        return isset($parts[3]) ? $parts[3][1] : $parts[2];
    }
}