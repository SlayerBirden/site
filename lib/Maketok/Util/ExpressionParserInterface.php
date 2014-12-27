<?php
/**
 * This is a part of Maketok site package.
 *
 * @author Oleg Kulik <slayer.birden@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Maketok\Util;

interface ExpressionParserInterface
{
    /**
     * @param string             $expression
     * @param array              $parameters
     * @param TokenizerInterface $tokenizer
     * @param array              $restrictions
     */
    public function __construct($expression, TokenizerInterface $tokenizer, array $parameters = [], array $restrictions = []);

    /**
     * glues tokenized variable and parameters
     * also checks for restrictions
     * @return string
     */
    public function evaluate();

    /**
     * Check if $newString satisfies the Expression
     * If it does, set the parameters
     *
     * @param  string     $newString
     * @return bool|array
     */
    public function parse($newString);

    /**
     * tokenize new string based on Tokenized parts
     * @param  string $string
     * @return array
     */
    public function tokenize($string);
}
