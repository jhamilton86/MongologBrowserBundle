<?php

namespace Mongolog\Bundle\MongologBrowserBundle\Services;


use Exception;
use Mongolog\Bundle\MongologBrowserBundle\Services\QueryParser\Compiler;
use Mongolog\Bundle\MongologBrowserBundle\Services\QueryParser\Exception\InvalidExpressionException;
use Mongolog\Bundle\MongologBrowserBundle\Services\QueryParser\Exception\UnbalancedParenthesisException;
use Mongolog\Bundle\MongologBrowserBundle\Services\QueryParser\Exception\UnexpectedClosingParenthesisException;
use Mongolog\Bundle\MongologBrowserBundle\Services\QueryParser\Parser;

class QueryParserTest extends \PHPUnit_Framework_TestCase {

    /**
     * @dataProvider provideEvaluations
     *
     * @param $string
     * @param $expected
     * @param Exception $expectedException
     */
    public function testEvaluations($string, $expected, Exception $expectedException = null)
    {
        if($expectedException)
        {
            $this->setExpectedException(get_class($expectedException), $expectedException->getMessage());
        }

        $parser = new Parser();
        $compiler = new Compiler();

        $array = $compiler->compile($parser->parse($string));

        $this->assertEquals($expected, $array);
    }

    public function provideEvaluations()
    {

        return array(
            array('', []),

            array('a > 9', [
                'a' => [
                    '$gt' => '9'
                ],
            ]),

            array('a ~= "foo bar @£$~+="', [
                'a' => [
                    '$regex' => 'foo bar @£$~+='
                ],
            ]),

            array('a > 9, b = x', [
                'a' => [
                    '$gt' => '9'
                ],
                'b' => [
                    '$eq' => 'x'
                ]
            ]),

            array('or[a > 9, b = x]', [
                '$or' => [
                    'a' => [
                        '$gt' => '9'
                    ],
                    'b' => [
                        '$eq' => 'x'
                    ]
                ]
            ]),

            // Silly but works...
            array('and[or[a > 9, b = x], c = d, c > 5, or[e = f]]', [
                '$and' => [
                    '$or' => [
                        'a' => [
                            '$gt' => '9'
                        ],
                        'b' => [
                            '$eq' => 'x'
                        ],
                        'e' => [
                            '$eq' => 'f'
                        ],
                    ],
                    'c' => [
                        '$eq' => 'd',
                        '$gt' => '5'
                    ],
                ]
            ]),

            // Dont work:
            array('a 9', [], new InvalidExpressionException('a 9')),
            array('and[a > 9', [], new UnbalancedParenthesisException(1)),
            array('or[and[a > 9', [], new UnbalancedParenthesisException(2)),
            array('and[or[and[a > 9]', [], new UnbalancedParenthesisException(2)),
            array('and[a > 9]]', [], new UnexpectedClosingParenthesisException()),
        );
    }
}