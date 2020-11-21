<?php
/**
 * Created by PhpStorm.
 * User: amalyuhin
 * Date: 24.01.14
 * Time: 18:43.
 */

namespace App\DQL\DatetimeFunction;

use Doctrine\ORM\Query\AST\Functions\FunctionNode;
use Doctrine\ORM\Query\Lexer;

/**
 * Class Year
 * @package App\DQL\DatetimeFunction
 */
class Year extends FunctionNode
{
    public $date;

    /**
     * @param \Doctrine\ORM\Query\SqlWalker $sqlWalker
     *
     * @return string
     */
    public function getSql(\Doctrine\ORM\Query\SqlWalker $sqlWalker)
    {
        return 'YEAR('.$sqlWalker->walkArithmeticPrimary($this->date).')';
    }

    /**
     * @param \Doctrine\ORM\Query\Parser $parser
     */
    public function parse(\Doctrine\ORM\Query\Parser $parser)
    {
        $parser->match(Lexer::T_IDENTIFIER);
        $parser->match(Lexer::T_OPEN_PARENTHESIS);

        $this->date = $parser->ArithmeticPrimary();

        $parser->match(Lexer::T_CLOSE_PARENTHESIS);
    }
}
