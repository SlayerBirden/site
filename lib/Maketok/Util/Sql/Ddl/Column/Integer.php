<?php
/**
 * This is a part of Maketok Site. Licensed under GPL 3.0
 * Please do not use for your own profit.
 * @project store
 * @developer Slayer slayer.birden@gmail.com maketok.com
 */
namespace Maketok\Util\Sql\Ddl\Column;

use Zend\Db\Sql\Ddl\Column;

class Integer extends Column\Integer
{

    /**
     * @return array
     */
    public function getExpressionData()
    {
        $spec = $this->specification;
        $options = $this->getOptions();

        $params   = array();
        $params[] = $this->name;
        $params[] = $this->type;

        $types = array(self::TYPE_IDENTIFIER, self::TYPE_LITERAL);

        // length

        if (isset($options['length']) && $options['length']) {
            $spec    .= '(%s)';
            $params[] = $options['length'];
            $types[]  = self::TYPE_LITERAL;
        }
        if (isset($options['unsigned']) && $options['unsigned']) {
            $spec    .= ' %s';
            $params[] = 'UNSIGNED';
            $types[]  = self::TYPE_LITERAL;
        }

        if (!$this->isNullable) {
            $spec    .= ' %s';
            $params[] = 'NOT NULL';
            $types[]  = self::TYPE_LITERAL;
        }

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
