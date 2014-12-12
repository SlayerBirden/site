<?php
/**
 * This is a part of Maketok Site. Licensed under GPL 3.0
 * @project site
 * @developer Oleg Kulik slayer.birden@gmail.com maketok.com
 */

namespace Maketok\Util;

interface TokenizerInterface
{

    /**
     * @param string $expression
     */
    public function __construct($expression);

    /**
     * Tokenizes expression
     * @return TokenizedBag|TokenizedBagPart[]
     */
    public function tokenize();
}
