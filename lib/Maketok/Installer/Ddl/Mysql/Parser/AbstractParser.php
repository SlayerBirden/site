<?php
/**
 * This is a part of Maketok Site. Licensed under GPL 3.0
 * @project site
 * @developer Oleg Kulik slayer.birden@gmail.com maketok.com
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
