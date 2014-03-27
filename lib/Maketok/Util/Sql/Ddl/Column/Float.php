<?php
/**
 * This is a part of Maketok Site. Licensed under GPL 3.0
 * Please do not use for your own profit.
 * @project store
 * @developer Slayer slayer.birden@gmail.com maketok.com
 */

namespace Maketok\Util\Sql\Ddl\Column;

use Zend\Db\Sql\Ddl\Column;

class Float extends Column\Float
{

    /**
     * @return array
     */
    public function getExpressionData()
    {
        $spec   = $this->specification;
        $params = array();

        $types      = array(self::TYPE_IDENTIFIER, self::TYPE_LITERAL);
        $params[]   = $this->name;
        $params[]   = $this->digits;
        $params[1] .= ', ' . $this->decimal;

        $types[]  = self::TYPE_LITERAL;
        $params[] = (!$this->isNullable) ? 'NOT NULL' : '';

        $types[]  = ($this->default !== null) ? self::TYPE_VALUE : self::TYPE_LITERAL;
        $params[] = ($this->default !== null) ? $this->default : '';

        $options = $this->getOptions();
        if (isset($options['increment']) && $options['increment']) {
            $spec    .= ' %s';
            $params[] = 'AUTO INCREMENT';
            $types[]  = self::TYPE_LITERAL;
        }

        return array(array(
            $spec,
            $params,
            $types,
        ));
    }
}
