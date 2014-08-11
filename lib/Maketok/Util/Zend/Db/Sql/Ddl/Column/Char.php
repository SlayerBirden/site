<?php
/**
 * This is a part of Maketok Site. Licensed under GPL 3.0
 * Please do not use for your own profit.
 * @project store
 * @developer Slayer slayer.birden@gmail.com maketok.com
 */
namespace Maketok\Util\Zend\Db\Sql\Ddl\Column;

use Zend\Db\Sql\Ddl\Column;

/**
 * Reload Char to add null and default
 *
 * @package Maketok\Util\Zend\Db\Sql\Ddl\Column
 */
class Char extends Column\Char
{

    /**
     * @var string
     */
    protected $specification = '%s CHAR(%s) %s';

    /**
     * @param null|string $name
     * @param int $length
     * @param bool $nullable
     * @param null $default
     */
    public function __construct($name, $length, $nullable = false, $default = null)
    {
        $this->name   = $name;
        $this->length = $length;
        $this->setNullable($nullable);
        $this->setDefault($default);
    }

    /**
     * @return array
     */
    public function getExpressionData()
    {
        $spec   = $this->specification;
        $params = array();

        $types    = array(self::TYPE_IDENTIFIER, self::TYPE_LITERAL);
        $params[] = $this->name;
        $params[] = $this->length;

        $types[]  = self::TYPE_LITERAL;
        $params[] = (!$this->isNullable) ? 'NOT NULL' : '';

        if ($this->default !== null) {
            $spec    .= ' DEFAULT %s';
            $params[] = $this->default;
            $types[]  = self::TYPE_VALUE;
        }

        return array(array(
            $spec,
            $params,
            $types,
        ));
    }

}
