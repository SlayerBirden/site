<?php
/**
 * This is a part of Maketok Site. Licensed under GPL 3.0
 * Please do not use for your own profit.
 * @project store
 * @developer Slayer slayer.birden@gmail.com maketok.com
 */

namespace Maketok\Util\Zend\Db\Sql\Platform\Mysql\Ddl;

use Zend\Db\Adapter\Platform\PlatformInterface;
use Zend\Db\Sql\Ddl\AlterTable;
use Zend\Db\Sql\Platform\PlatformDecoratorInterface;

class AlterTableDecorator extends AlterTable implements PlatformDecoratorInterface
{
    /**
     * @var AlterTable
     */
    protected $alterTable;

    /**
     * @param AlterTable $subject
     */
    public function setSubject($subject)
    {
        $this->alterTable = $subject;
    }

    /**
     * @param  null|PlatformInterface $platform
     * @return string
     */
    public function getSqlString(PlatformInterface $platform = null)
    {
        // localize variables
        foreach (get_object_vars($this->alterTable) as $name => $value) {
            $this->{$name} = $value;
        }
        return parent::getSqlString($platform);
    }

    protected function processAddColumns(PlatformInterface $adapterPlatform = null)
    {
        $sqls = array();
        foreach ($this->addColumns as $i => $column) {
            $stmtContainer = $this->processExpression($column, $adapterPlatform);
            $sql           = $stmtContainer->getSql();
            $columnOptions = $column->getOptions();

            foreach ($columnOptions as $coName => $coValue) {
                switch (strtolower(str_replace(array('-', '_', ' '), '', $coName))) {
                    case 'identity':
                    case 'serial':
                    case 'autoincrement':
                        $sql .= ' AUTO_INCREMENT';
                        break;
                    case 'comment':
                        $sql .= ' COMMENT \'' . $coValue . '\'';
                        break;
                    case 'columnformat':
                    case 'format':
                        $sql .= ' COLUMN_FORMAT ' . strtoupper($coValue);
                        break;
                    case 'storage':
                        $sql .= ' STORAGE ' . strtoupper($coValue);
                        break;
                }
            }
            $sqls[$i] = $sql;
        }
        return array($sqls);
    }

    protected function processChangeColumns(PlatformInterface $adapterPlatform = null)
    {
        $sqls = array();
        foreach ($this->changeColumns as $name => $column) {
            $stmtContainer = $this->processExpression($column, $adapterPlatform);
            $sql           = $stmtContainer->getSql();
            $columnOptions = $column->getOptions();

            foreach ($columnOptions as $coName => $coValue) {
                switch (strtolower(str_replace(array('-', '_', ' '), '', $coName))) {
                    case 'identity':
                    case 'serial':
                    case 'autoincrement':
                        $sql .= ' AUTO_INCREMENT';
                        break;
                    case 'comment':
                        $sql .= ' COMMENT \'' . $coValue . '\'';
                        break;
                    case 'columnformat':
                    case 'format':
                        $sql .= ' COLUMN_FORMAT ' . strtoupper($coValue);
                        break;
                    case 'storage':
                        $sql .= ' STORAGE ' . strtoupper($coValue);
                        break;
                }
            }
            $sqls[] = array(
                $adapterPlatform->quoteIdentifier($name),
                $sql
            );
        }

        return array($sqls);
    }
}
