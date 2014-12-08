<?php
/**
 * This is a part of Maketok Site. Licensed under GPL 3.0
 * Please do not use for your own profit.
 * @project site
 * @developer Slayer slayer.birden@gmail.com maketok.com
 */

namespace Maketok\Installer\Ddl\Mysql\Parser;

interface ParserInterface
{

    /**
     * @param string $row
     * @param string $name
     */
    public function __construct($row, $name = null);

    /**
     * @return array
     */
    public function parse();
}
