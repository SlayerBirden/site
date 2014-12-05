<?php
/**
 * This is a part of Maketok Site. Licensed under GPL 3.0
 * Please do not use for your own profit.
 * @project site
 * @developer Slayer slayer.birden@gmail.com maketok.com
 */

namespace Maketok\Util;


interface ExpressionParserInterface
{

    /**
     * @param null|array $parameters
     * @param null|array $restrictions
     * @return mixed|string
     */
    public function evaluate($parameters = null, $restrictions = null);

    /**
     * Check if $newString satisfies the Expression
     * If it does, set the parameters
     *
     * @param string $newString
     * @param null|array $restrictions
     * @return bool|array
     */
    public function parse($newString, $restrictions = null);

    /**
     * tries to tokenize the expression
     * @param null|string $string
     * @return array
     */
    public function tokenize($string = null);
}
