<?php
/**
 * This is a part of Maketok Site. Licensed under GPL 3.0
 * @project site
 * @developer Oleg Kulik slayer.birden@gmail.com maketok.com
 */

namespace Maketok\Util;


interface ExpressionParserInterface
{

    /**
     * @param string $expression
     * @param array $parameters
     * @param TokenizerInterface $tokenizer
     * @param array $restrictions
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
     * @param string $newString
     * @return bool|array
     */
    public function parse($newString);

    /**
     * tokenize new string based on Tokenized parts
     * @param string $string
     * @return array
     */
    public function tokenize($string);
}
