<?php
/**
 * This is a part of Maketok Site. Licensed under GPL 3.0
 * Please do not use for your own profit.
 * @project site
 * @developer Slayer slayer.birden@gmail.com maketok.com
 */

namespace Maketok\Installer\Ddl\Mysql\Parser;

abstract class AbstractParser implements ParserInterface
{
    /**
     * @var string
     */
    protected $row;
    /**
     * @var null|string
     */
    protected $name;

    /**
     * {@inheritdoc}
     */
    public function __construct($row, $name = null)
    {
        $this->row = $row;
        $this->name = $name;
    }
}
