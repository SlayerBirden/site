<?php
/**
 * This is a part of Maketok Site. Licensed under GPL 3.0
 *
 * @project site
 * @developer Slayer slayer.birden@gmail.com maketok.com
 */

namespace Maketok\Util\Zend\Db\Sql;


use Zend\Db\Adapter\AdapterInterface;
use Zend\Db\Adapter\ParameterContainer;
use Zend\Db\Adapter\Platform\PlatformInterface;
use Zend\Db\Adapter\Platform\Sql92;
use Zend\Db\Adapter\StatementContainerInterface;
use Zend\Db\Sql\Expression;
use Zend\Db\Sql\Insert;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Exception;
use Zend\Db\Sql\TableIdentifier;

class InsertDuplicate extends Insert
{

    /**
     * @var array Specification array
     */
    protected $specifications = array(
        self::SPECIFICATION_INSERT => 'INSERT INTO %1$s (%2$s) VALUES (%3$s) %4$s',
        self::SPECIFICATION_SELECT => 'INSERT INTO %1$s %2$s %3$s %4$s',
    );


    /**
     * Prepare statement
     *
     * @param  AdapterInterface $adapter
     * @param  StatementContainerInterface $statementContainer
     * @throws \Zend\Db\Sql\Exception\InvalidArgumentException
     * @return void
     */
    public function prepareStatement(AdapterInterface $adapter, StatementContainerInterface $statementContainer)
    {
        $driver   = $adapter->getDriver();
        $platform = $adapter->getPlatform();
        $parameterContainer = $statementContainer->getParameterContainer();

        if (!$parameterContainer instanceof ParameterContainer) {
            $parameterContainer = new ParameterContainer();
            $statementContainer->setParameterContainer($parameterContainer);
        }

        $table = $this->table;
        $schema = null;

        // create quoted table name to use in insert processing
        if ($table instanceof TableIdentifier) {
            list($table, $schema) = $table->getTableAndSchema();
        }

        $table = $platform->quoteIdentifier($table);

        if ($schema) {
            $table = $platform->quoteIdentifier($schema) . $platform->getIdentifierSeparator() . $table;
        }

        $columns = array();
        $values  = array();
        $duplicateArray = array();

        if (is_array($this->values)) {
            foreach ($this->columns as $cIndex => $column) {
                $columns[$cIndex] = $platform->quoteIdentifier($column);
                if (isset($this->values[$cIndex]) && $this->values[$cIndex] instanceof Expression) {
                    $exprData = $this->processExpression($this->values[$cIndex], $platform, $driver);
                    $values[$cIndex] = $exprData->getSql();
                    $parameterContainer->merge($exprData->getParameterContainer());
                } else {
                    $values[$cIndex] = $driver->formatParameterName($column);
                    if (isset($this->values[$cIndex])) {
                        $parameterContainer->offsetSet($column, $this->values[$cIndex]);
                    } else {
                        $parameterContainer->offsetSet($column, null);
                    }
                }
                // add duplicate array
                $duplicateArray = "{$columns[$cIndex]} = VALUES({$columns[$cIndex]})";
            }
            $sql = sprintf(
                $this->specifications[static::SPECIFICATION_INSERT],
                $table,
                implode(', ', $columns),
                implode(', ', $values),
                "ON DUPLICATE KEY UPDATE ". implode(', ', $duplicateArray)
            );
        } elseif ($this->values instanceof Select) {
            $this->values->prepareStatement($adapter, $statementContainer);

            $columns = array_map(array($platform, 'quoteIdentifier'), $this->columns);
            foreach ($columns as $column) {
                $duplicateArray = "{$column} = VALUES({$column})";
            }
            $columns = implode(', ', $columns);


            $sql = sprintf(
                $this->specifications[static::SPECIFICATION_SELECT],
                $table,
                $columns ? "($columns)" : "",
                $statementContainer->getSql(),
                "ON DUPLICATE KEY UPDATE ". implode(', ', $duplicateArray)
            );
        } else {
            throw new Exception\InvalidArgumentException('values or select should be present');
        }
        $statementContainer->setSql($sql);
    }

    /**
     * Get SQL string for this statement
     *
     * @param  null|PlatformInterface $adapterPlatform Defaults to Sql92 if none provided
     * @throws Exception\InvalidArgumentException
     * @return string
     */
    public function getSqlString(PlatformInterface $adapterPlatform = null)
    {
        $adapterPlatform = ($adapterPlatform) ?: new Sql92;
        $table = $this->table;
        $schema = null;

        // create quoted table name to use in insert processing
        if ($table instanceof TableIdentifier) {
            list($table, $schema) = $table->getTableAndSchema();
        }

        $table = $adapterPlatform->quoteIdentifier($table);

        if ($schema) {
            $table = $adapterPlatform->quoteIdentifier($schema) . $adapterPlatform->getIdentifierSeparator() . $table;
        }
        $duplicateArray = array();

        $columns = array_map(array($adapterPlatform, 'quoteIdentifier'), $this->columns);
        foreach ($columns as $column) {
            $duplicateArray = "{$column} = VALUES({$column})";
        }
        $columns = implode(', ', $columns);

        if (is_array($this->values)) {
            $values = array();
            foreach ($this->values as $value) {
                if ($value instanceof Expression) {
                    $exprData = $this->processExpression($value, $adapterPlatform);
                    $values[] = $exprData->getSql();
                } elseif ($value === null) {
                    $values[] = 'NULL';
                } else {
                    $values[] = $adapterPlatform->quoteValue($value);
                }
            }
            return sprintf(
                $this->specifications[static::SPECIFICATION_INSERT],
                $table,
                $columns,
                implode(', ', $values),
                "ON DUPLICATE KEY UPDATE ". implode(', ', $duplicateArray)
            );
        } elseif ($this->values instanceof Select) {
            $selectString = $this->values->getSqlString($adapterPlatform);
            if ($columns) {
                $columns = "($columns)";
            }
            return sprintf(
                $this->specifications[static::SPECIFICATION_SELECT],
                $table,
                $columns,
                $selectString,
                "ON DUPLICATE KEY UPDATE ". implode(', ', $duplicateArray)
            );
        } else {
            throw new Exception\InvalidArgumentException('values or select should be present');
        }
    }
}
