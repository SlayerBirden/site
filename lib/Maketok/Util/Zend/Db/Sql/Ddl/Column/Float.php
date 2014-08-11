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
 * Class Float add zerofill, unsigned attributes
 * coming in options array
 * @package Maketok\Util\Zend\Db\Sql\Ddl\Column
 */
class Float extends Column\Float
{

    /**
     * @param null|string $name
     * @param int $digits
     * @param int $decimal
     * @param array|null $options
     */
    public function __construct($name, $digits, $decimal, array $options = null)
    {
        $this->name    = $name;
        $this->digits  = $digits;
        $this->decimal = $decimal;
        if (is_null($options)) {
            $options = array();
        }
        $this->setOptions($options);
    }

    /**
     * @return array
     */
    public function getExpressionData()
    {
        $spec   = $this->specification;
        $params = array();
        $options = $this->getOptions();

        $types      = array(self::TYPE_IDENTIFIER, self::TYPE_LITERAL);
        $params[]   = $this->name;
        $params[]   = $this->digits;
        $params[1] .= ', ' . $this->decimal;

        if (isset($options['zerofill']) && $options['zerofill']) {
            $spec    .= ' %s';
            $params[] = 'ZEROFILL';
            $types[]  = self::TYPE_LITERAL;
        }

        if (isset($options['unsigned']) && $options['unsigned']) {
            $spec    .= ' %s';
            $params[] = 'UNSIGNED';
            $types[]  = self::TYPE_LITERAL;
        }

        $types[]  = self::TYPE_LITERAL;
        $params[] = (!$this->isNullable) ? 'NOT NULL' : '';

        $types[]  = ($this->default !== null) ? self::TYPE_VALUE : self::TYPE_LITERAL;
        $params[] = ($this->default !== null) ? $this->default : '';

        return array(array(
            $spec,
            $params,
            $types,
        ));
    }

}
