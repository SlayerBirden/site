<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2014 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Db\Sql\Ddl;

use Zend\Db\Adapter\Platform\PlatformInterface;
use Zend\Db\Sql\AbstractSql;

class AlterTable extends AbstractSql implements SqlInterface
{
    const ADD_COLUMNS      = 'addColumns';
    const ADD_CONSTRAINTS  = 'addConstraints';
    const CHANGE_COLUMNS   = 'changeColumns';
    const DROP_COLUMNS     = 'dropColumns';
    const DROP_FK          = 'dropFK';
    const DROP_INDEXES     = 'dropIndexes';
    const DROP_PK          = 'dropPK';
    const TABLE            = 'table';

    /**
     * @var array
     */
    protected $addColumns = array();

    /**
     * @var array
     */
    protected $addConstraints = array();

    /**
     * @var array
     */
    protected $changeColumns = array();

    /**
     * @var array
     */
    protected $dropColumns = array();

    /**
     * @var array
     */
    protected $dropFk = array();

    /**
     * @var array
     */
    protected $dropIndex = array();

    /**
     * @var array
     */
    protected $dropPk = array();

    /**
     * Specifications for Sql String generation
     * @var array
     */
    protected $specifications = array(
        self::TABLE => "ALTER TABLE %1\$s\n",
        self::ADD_COLUMNS  => array(
            "%1\$s" => array(
                array(1 => "ADD COLUMN %1\$s,\n", 'combinedby' => "")
            )
        ),
        self::CHANGE_COLUMNS  => array(
            "%1\$s" => array(
                array(2 => "CHANGE COLUMN %1\$s %2\$s,\n", 'combinedby' => ""),
            )
        ),
        self::DROP_COLUMNS  => array(
            "%1\$s" => array(
                array(1 => "DROP COLUMN %1\$s,\n", 'combinedby' => ""),
            )
        ),
        self::ADD_CONSTRAINTS  => array(
            "%1\$s" => array(
                array(1 => "ADD %1\$s,\n", 'combinedby' => ""),
            )
        ),
        self::DROP_PK  => array(
            "%1\$s" => array(
                array(1 => "DROP PRIMARY KEY,\n", 'combinedby' => ""),
            )
        ),
        self::DROP_FK  => array(
            "%1\$s" => array(
                array(1 => "DROP FOREIGN KEY %1\$s,\n", 'combinedby' => ""),
            )
        ),
        self::DROP_INDEXES  => array(
            "%1\$s" => array(
                array(1 => "DROP INDEX %1\$s,\n", 'combinedby' => ""),
            )
        )
    );

    /**
     * @var string
     */
    protected $table = '';

    /**
     * @param string $table
     */
    public function __construct($table = '')
    {
        ($table) ? $this->setTable($table) : null;
    }

    /**
     * @param  string $name
     * @return self
     */
    public function setTable($name)
    {
        $this->table = $name;

        return $this;
    }

    /**
     * @param  Column\ColumnInterface $column
     * @return self
     */
    public function addColumn(Column\ColumnInterface $column)
    {
        $this->addColumns[] = $column;

        return $this;
    }

    /**
     * @param  string $name
     * @param  Column\ColumnInterface $column
     * @return self
     */
    public function changeColumn($name, Column\ColumnInterface $column)
    {
        $this->changeColumns[$name] = $column;

        return $this;
    }

    /**
     * @param  string $name
     * @return self
     */
    public function dropColumn($name)
    {
        $this->dropColumns[] = $name;

        return $this;
    }

    /**
     * @param  string $name
     * @return self
     */
    public function dropFk($name)
    {
        $this->dropFk[] = $name;

        return $this;
    }

    /**
     * @param  string $name
     * @return self
     */
    public function dropIndex($name)
    {
        $this->dropIndex[] = $name;

        return $this;
    }

    /**
     * @param  string|null $name
     * @return self
     */
    public function dropPk($name = null)
    {
        $this->dropPk[] = $name;

        return $this;
    }

    /**
     * @param  Constraint\ConstraintInterface $constraint
     * @return self
     */
    public function addConstraint(Constraint\ConstraintInterface $constraint)
    {
        $this->addConstraints[] = $constraint;

        return $this;
    }

    /**
     * @param  string|null $key
     * @return array
     */
    public function getRawState($key = null)
    {
        $rawState = array(
            self::TABLE => $this->table,
            self::ADD_COLUMNS => $this->addColumns,
            self::DROP_COLUMNS => $this->dropColumns,
            self::CHANGE_COLUMNS => $this->changeColumns,
            self::ADD_CONSTRAINTS => $this->addConstraints,
            self::DROP_PK => $this->dropPk,
            self::DROP_FK => $this->dropFk,
            self::DROP_INDEXES => $this->dropIndex,
        );

        return (isset($key) && array_key_exists($key, $rawState)) ? $rawState[$key] : $rawState;
    }

    protected function processTable(PlatformInterface $adapterPlatform = null)
    {
        return array($adapterPlatform->quoteIdentifier($this->table));
    }

    protected function processAddColumns(PlatformInterface $adapterPlatform = null)
    {
        $sqls = array();
        foreach ($this->addColumns as $column) {
            $sqls[] = $this->processExpression($column, $adapterPlatform);
        }

        return array($sqls);
    }

    protected function processChangeColumns(PlatformInterface $adapterPlatform = null)
    {
        $sqls = array();
        foreach ($this->changeColumns as $name => $column) {
            $sqls[] = array(
                $adapterPlatform->quoteIdentifier($name),
                $this->processExpression($column, $adapterPlatform)
            );
        }

        return array($sqls);
    }

    protected function processDropColumns(PlatformInterface $adapterPlatform = null)
    {
        $sqls = array();
        foreach ($this->dropColumns as $column) {
            $sqls[] = $adapterPlatform->quoteIdentifier($column);
        }

        return array($sqls);
    }

    protected function processAddConstraints(PlatformInterface $adapterPlatform = null)
    {
        $sqls = array();
        foreach ($this->addConstraints as $constraint) {
            $sqls[] = $this->processExpression($constraint, $adapterPlatform);
        }

        return array($sqls);
    }

    protected function processDropPK(PlatformInterface $adapterPlatform = null)
    {
        $sqls = array();
        foreach ($this->dropPk as $constraint) {
            $sqls[] = $adapterPlatform->quoteIdentifier($constraint);
        }

        return array($sqls);
    }

    protected function processDropFK(PlatformInterface $adapterPlatform = null)
    {
        $sqls = array();
        foreach ($this->dropFk as $constraint) {
            $sqls[] = $adapterPlatform->quoteIdentifier($constraint);
        }

        return array($sqls);
    }

    protected function processDropIndexes(PlatformInterface $adapterPlatform = null)
    {
        $sqls = array();
        foreach ($this->dropIndex as $constraint) {
            $sqls[] = $adapterPlatform->quoteIdentifier($constraint);
        }

        return array($sqls);
    }
}
