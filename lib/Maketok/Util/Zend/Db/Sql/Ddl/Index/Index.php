<?php
/**
 * This is a part of Maketok Site. Licensed under GPL 3.0
 * Please do not use for your own profit.
 * @project store
 * @developer Slayer slayer.birden@gmail.com maketok.com
 */

namespace Maketok\Util\Zend\Db\Sql\Ddl\Index;

class Index extends AbstractIndex
{
    /**
     * @var string
     */
    protected $specification = 'INDEX %s(...)';

    /**
     * @param  string $column
     * @param  null|string $name
     */
    public function __construct($column, $name = null)
    {
        $this->setColumns($column);
        $this->name = $name;
    }

    /**
     *
     * @return array of array|string should return an array in the format:
     *
     * array (
     *    // a sprintf formatted string
     *    string $specification,
     *
     *    // the values for the above sprintf formatted string
     *    array $values,
     *
     *    // an array of equal length of the $values array, with either TYPE_IDENTIFIER or TYPE_VALUE for each value
     *    array $types,
     * )
     *
     */
    public function getExpressionData()
    {
        $colCount = count($this->columns);

        $values   = array();
        $values[] = ($this->name) ? $this->name : '';

        $newSpecTypes = array(self::TYPE_IDENTIFIER);
        $newSpecParts = array();

        for ($i = 0; $i < $colCount; $i++) {
            $newSpecParts[] = '%s';
            $newSpecTypes[] = self::TYPE_IDENTIFIER;
        }

        $newSpec = str_replace('...', implode(', ', $newSpecParts), $this->specification);

        return array(array(
            $newSpec,
            array_merge($values, $this->columns),
            $newSpecTypes,
        ));
    }
}