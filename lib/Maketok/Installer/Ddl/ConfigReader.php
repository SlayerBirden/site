<?php
/**
 * This is a part of Maketok Site. Licensed under GPL 3.0
 * Please do not use for your own profit.
 * @project site
 * @developer Slayer slayer.birden@gmail.com maketok.com
 */

namespace Maketok\Installer\Ddl;

use Maketok\Installer\ConfigReaderInterface;

class ConfigReader implements ConfigReaderInterface
{

    /** @var array */
    protected  $_availableConstraintTypes = ['primaryKey', 'uniqueKey', 'foreignKey'];
    /**
     * [
     *  'tables' => [
     *      'add' => [],
     *      'remove' => [],
     *      'update' => [
     *          'tableName' => [
     *              'columns' => [
     *                  'add' => [],
     *                  'remove' => [],
     *                  'update' => [],
     *              ],
     *              'constraints' => [
     *                  'add' => [],
     *                  'remove' => [],
     *              ],
     *              'indices' => [
     *                  'add' => [],
     *                  'remove' => [],
     *              ],
     *          ],
     *      ],
     *  ],
     * ]
     *
     * @var array
     */
    protected $_directives = [
        'tables' => [
            'add' => [],
            'remove' => [],
            'update' => [],
        ],
    ];

    const SYMBOL_ADD = '*';
    const SYMBOL_REMOVE = '~';
    const SYMBOL_ID = '&';

    const TYPE_ADD = 0b1;
    const TYPE_REMOVE = 0b10;
    const TYPE_ID = 0b100;
    const TYPE_UPDATE = 0b1000;

    /**
     * @var ResourceInterface
     */
    private $_resource;

    public function __construct(ResourceInterface $resource)
    {
        $this->_resource = $resource;
    }

    /**
     * @param array $configChain
     * @return void
     */
    public function processConfig(array $configChain)
    {
        foreach ($configChain as $config) {
            // nest layer is tables
            foreach ($config as $table => $definition) {
                $_type = $this->_getType($table);
                if ($_type & self::TYPE_ADD) {
                    $this->addTableDirective($table, 'add', $definition);
                } elseif ($_type & self::TYPE_REMOVE) {
                    $this->addTableDirective($table, 'remove', $definition);
                } else {
                    // update case
                    // next layer is columns/constraints/indices
                    foreach ($definition as $entityType => $entities) {
                        foreach ($entities as $entityName => $entityDefinition) {
                            $_type = $this->_getType($entityName);
                            if ($_type & self::TYPE_ADD) {
                                $this->addTableEntityTypeDirective($entityType, $table, 'add', $entityName, $entityDefinition);
                            } elseif ($_type & self::TYPE_REMOVE) {
                                $this->addTableEntityTypeDirective($entityType, $table, 'remove', $entityName, $entityDefinition);
                            } elseif ($_type & self::TYPE_UPDATE || $_type & self::TYPE_ID) {
                                $this->addTableEntityTypeDirective($entityType, $table, 'update', $entityName, $entityDefinition);
                            }
                        }
                    }
                }
            }
        }
    }

    /**
     * @param string $table
     * @param string $operation
     * @param string $tableDef
     */
    public function addTableDirective($table, $operation, $tableDef)
    {
        $this->_directives['tables'][$operation][$this->strip($table)] = $tableDef;
    }

    /**
     * @param string $type
     * @param string $table
     * @param string $operation
     * @param string $name
     * @param string $def
     * @throws \InvalidArgumentException
     */
    public function addTableEntityTypeDirective($type, $table, $operation, $name, $def)
    {
        if ($type != 'columns' && $type != 'constraints' && $type != 'indices') {
            throw new \InvalidArgumentException(sprintf("Can not add entity: invalid type %s", $type));
        }
        $this->_directives['tables']['update'][$table][$type][$operation][$this->strip($name)] = $def;
    }

    /**
     * @param string $string
     * @return string
     */
    public function strip($string)
    {
        return ltrim($string, self::SYMBOL_ADD . self::SYMBOL_REMOVE);
    }

    /**
     * @param string $table
     * @param string $operation
     * @param string $columnName
     * @param string $columnDef
     */
    public function addColumnDirective($table, $operation, $columnName, $columnDef)
    {
        $this->addTableEntityTypeDirective('columns', $table, $operation, $columnName, $columnDef);
    }

    /**
     * @param string $table
     * @param string $operation
     * @param string $constraintName
     * @param string $constraintDef
     */
    public function addConstraintDirective($table, $operation, $constraintName, $constraintDef)
    {
        $this->addTableEntityTypeDirective('constraints', $table, $operation, $constraintName, $constraintDef);
    }

    /**
     * @param string $table
     * @param string $operation
     * @param string $indexName
     * @param string $indexDef
     */
    public function addIndexDirective($table, $operation, $indexName, $indexDef)
    {
        $this->addTableEntityTypeDirective('indices', $table, $operation, $indexName, $indexDef);
    }

    /**
     * @param string $string
     * @throws \LogicException
     * @return int
     */
    protected function _getType($string)
    {
        $res = 0;
        for ($i = 0; $i < 3; ++$i) {
            switch (substr($string, $i, 1)) {
                case self::SYMBOL_ADD:
                    $res = $res | self::TYPE_ADD;
                    break;
                case self::SYMBOL_REMOVE:
                    $res = $res | self::TYPE_REMOVE;
                    break;
                case self::SYMBOL_ID:
                    $res = $res | self::TYPE_ID;
                    break;
            }
        }
        if ($res === 0) {
            $res = $res | self::TYPE_UPDATE;
        }
        // little validation
        if ($res & self::TYPE_ADD && $res & self::TYPE_REMOVE) {
            throw new \LogicException("Directive can not be add and remove at the same time.");
        }
        return $res;
    }

    /**
     * @return void
     */
    public function validateDirectives()
    {
        // TODO: Implement validateDirectives() method.
    }

    /**
     * @return array
     */
    public function getDirectives()
    {
        return $this->_directives;
    }

    /**
     * @return void
     */
    public function compileDirectives()
    {
        // TODO: Implement compileDirectives() method.
    }
}
