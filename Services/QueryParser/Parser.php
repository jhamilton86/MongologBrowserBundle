<?php namespace Mongolog\Bundle\MongologBrowserBundle\Services\QueryParser;

use Mongolog\Bundle\MongologBrowserBundle\Services\QueryParser\Compiler\Token\LogicalOperator;
use Mongolog\Bundle\MongologBrowserBundle\Services\QueryParser\Compiler\Token\Operator;
use Phlexy\LexerDataGenerator;
use Phlexy\LexerFactory\Stateless\UsingPregReplace;

class Parser
{
    protected $lexer;

    const TOK_WHITESPACE = 'whitespace';

    const TOK_RBRACKET = 'closing bracket';

    const TOK_LITERAL = 'literal';

    const TOK_STRING_LITERAL = 'string literal';

    const TOK_INT_LITERAL = 'int literal';

    const TOK_FLOAT_LITERAL = 'float literal';

    const TOK_EXPRESSION_SPLIT = ',';

    public function __construct()
    {
        $factory = new UsingPregReplace(new LexerDataGenerator);

        $this->lexer = $factory->createLexer($this->getConfig());
    }

    private function getConfig()
    {
        $tokens = array(

            '\]'                            => self::TOK_RBRACKET,
            'and\['                         => new LogicalOperator('$and'),
            'AND\['                         => new LogicalOperator('$and'),
            'or\['                          => new LogicalOperator('$or'),
            'OR\['                          => new LogicalOperator('$or'),
            '>='                            => new Operator('$gte'),
            '<='                            => new Operator('$lte'),
            '<'                             => new Operator('$lt'),
            '>'                             => new Operator('$gt'),
            '='                             => new Operator('$eq'),
            '=='                            => new Operator('$eq'),
            '!='                            => new Operator('$ne'),
            '<>'                            => new Operator('$ne'),
            '~='                            => new Operator('$regex'),
            ','                             => self::TOK_EXPRESSION_SPLIT,
            '\s+'                           => self::TOK_WHITESPACE,
        );

        foreach($this->getLiterals() as $literal => $token)
        {
            $tokens[$literal] = $token;
        }

        return $tokens;
    }

    private function getLiterals()
    {
        return array(
            '(?:\s+)?(\d+)(?:\s+)?'             => self::TOK_INT_LITERAL,
            '(?:\s+)?(\d+\.\d+)(?:\s+)?'        => self::TOK_FLOAT_LITERAL,
            '(?:\s+)?\'(.+?)\'(?:\s+)?'         => self::TOK_STRING_LITERAL,
            '(?:\s+)?"(.+?)"(?:\s+)?'           => self::TOK_STRING_LITERAL,
            '(?:\s+)?([A-Za-z0-9_\.]+)(?:\s+)?' => self::TOK_LITERAL,
        );
    }

    public function isSimpleLiteral($string)
    {
        foreach($this->getLiterals() as $literal => $token)
        {
            if(preg_match("/^$literal$/", $string, $matches)){
                return $matches;
            }
        }

        return false;
    }

    public function parse($string)
    {
        return $this->lexer->lex($string);
    }
}
