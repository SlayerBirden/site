<?php
/**
 * This is a part of Maketok Site. Licensed under GPL 3.0
 * Please do not use for your own profit.
 * @project store
 * @developer Slayer slayer.birden@gmail.com maketok.com
 */
namespace Maketok\App\Ddl;

use Maketok\App\Site;
use Maketok\Util\Sql\Ddl\Column\Blob;
use Maketok\Util\Sql\Ddl\Column\Datetime;
use Maketok\Util\Sql\Ddl\Column;
use Maketok\Util\Sql\Ddl\Column\Float;
use Maketok\Util\Sql\Platform\Platform;
use Maketok\Util\StreamHandler;
use Zend\Db\Sql\Ddl\CreateTable;
use Zend\Db\Sql\Ddl\AlterTable;
use Zend\Db\Sql\Ddl\DropTable;
use Zend\Db\Sql\Ddl\SqlInterface;
use Zend\Db\Sql\Platform\AbstractPlatform;
use Zend\Db\Sql\Sql;

class Installer
{

    const EXCEPTION_DDL_CODE = 300;

    /**
     * @var string
     */
    private static $_installerLockSheetName = 'ddl_installer.lock';
    private static $_lockStreamHandler;
    private $_clients = array();

    /**
     * @var AbstractPlatform
     */
    private static $_platform;

    /**
     * @var Sql
     */
    private static $_sql;
    /**
     * @var \ArrayObject
     */
    private static $_map;

    static $_availableConstraintTypes = ['primaryKey', 'uniqueKey', 'foreignKey', 'index'];

    /**
     * @param string $tableName
     * @param array $tableDefinition
     * @throws InstallerException
     */
    private static function _addTable($tableName, array $tableDefinition)
    {
        $table = new CreateTable($tableName);
        if (!isset($tableDefinition['columns']) || !is_array($tableDefinition['columns'])) {
            throw new InstallerException(sprintf('Can not create a table `%s` without columns definition.', $tableName),
                self::EXCEPTION_DDL_CODE);
        }
        $_columns = $tableDefinition['columns'];
        $_constraints = isset($tableDefinition['constraints']) ? $tableDefinition['constraints'] : array();
        foreach ($_columns as $columnName => $columnDefinition) {
            self::_addColumn($tableName, $columnName, $columnDefinition, $table);
        }
        foreach ($_constraints as $constraintName => $constraintDefinition) {
            self::_addConstraint($tableName, $constraintName, $constraintDefinition, $table);
        }
        self::_commit($table);
    }

    /**
     * @param string $tableName
     * @param string $columnName
     * @param array $columnDefinition
     * @param null|CreateTable|AlterTable $table
     */
    private static function _addColumn($tableName, $columnName, array $columnDefinition, $table = null)
    {
        $_commitFlag = false;
        if (is_null($table)) {
            $table = new AlterTable($tableName);
            $_commitFlag = true;
        }
        $column = self::_getInitColumn($columnName, $columnDefinition);
        $table->addColumn($column);
        if ($_commitFlag) {
            self::_commit($table);
        }
    }

    /**
     * @param string $tableName
     * @param string $constraintName
     * @param array $constraintDefinition
     * @param null|CreateTable|AlterTable $table
     * @throws InstallerException
     */
    private static function _addConstraint($tableName, $constraintName, array $constraintDefinition, $table = null)
    {
        $_commitFlag = false;
        if (is_null($table)) {
            $table = new AlterTable($tableName);
            $_commitFlag = true;
        }
        if (!isset($constraintDefinition['type']) || !in_array($constraintDefinition['type'], self::$_availableConstraintTypes)) {
            // can't create constraint or unavailable constraint type
            throw new InstallerException(sprintf('Can not create constraint %s for table %s. Missing or unavailable type.', $constraintName, $tableName));
        }
        /** @var \Zend\Db\Sql\Ddl\Constraint\ConstraintInterface $type */
        $type = '\\Zend\\Db\\Sql\\Ddl\\Constraint\\' . ucfirst($constraintDefinition['type']);
        if ($constraintDefinition['type'] == 'index') {
            /** @var \Maketok\Util\Sql\Ddl\Index\Index $type */
            $type = '\\Maketok\\Util\\Sql\\Ddl\\Index\\Index';
        }
        if ($constraintDefinition['type'] == 'foreignKey') {
            $column = $constraintDefinition['def'];
            $refTable = $constraintDefinition['referenceTable'];
            $refColumn = $constraintDefinition['referenceColumn'];
            $onDelete = (isset($constraintDefinition['onDelete']) ? $constraintDefinition['onDelete'] : 'CASCADE');
            $onUpdate = (isset($constraintDefinition['onUpdate']) ? $constraintDefinition['onUpdate'] : 'CASCADE');
            $constraint = new $type($constraintName, $column, $refTable, $refColumn, $onDelete, $onUpdate);
        } else {
            $constraint = new $type($constraintDefinition['def'], $constraintName);
        }

        $table->addConstraint($constraint);
        if ($_commitFlag) {
            self::_commit($table);
        }
    }

    /**
     * @param CreateTable|AlterTable|DropTable|SqlInterface $ddl
     * @param string $directQuery
     */
    private static function _commit(SqlInterface $ddl, $directQuery = null)
    {
        $adapter = Site::getAdapter();
        $query = $directQuery ?: self::_getQuery($ddl);

        $adapter->query(
            $query,
            $adapter::QUERY_MODE_EXECUTE
        );
    }

    /**
     * @return AbstractPlatform
     */
    private static function _getPlatform()
    {
        if (!isset(self::$_platform)) {
            self::$_platform = new Platform(Site::getAdapter());
        }
        return self::$_platform;
    }

    /**
     * @return Sql
     */
    private static function _getSql()
    {
        if (!isset(self::$_sql)) {
            self::$_sql = new Sql(Site::getAdapter(), null, self::_getPlatform());
        }
        return self::$_sql;
    }

    /**
     * @param string $tableName
     */
    private static function _dropTable($tableName)
    {
        $table = new DropTable($tableName);
        self::_commit($table);
    }

    /**
     * @param string $tableName
     * @param string $columnName
     */
    private static function _dropColumn($tableName, $columnName)
    {
        $table = new AlterTable($tableName);
        $table->dropColumn($columnName);
        self::_commit($table);
    }

    /**
     * @param string $tableName
     * @param string $constraintName
     */
    private static function _dropConstraint($tableName, $constraintName)
    {
        $table = new AlterTable($tableName);
        $table->dropConstraint($constraintName);
        // big thanks to MySQL for this hack!!
        $query = self::_getQuery($table);
        $query = str_replace('CONSTRAINT', 'FOREIGN KEY', $query);
        self::_commit($table, $query);
    }

    /**
     * @param SqlInterface $table
     * @return mixed
     */
    private static function _getQuery(SqlInterface $table)
    {
        return self::_getSql()->getSqlStringForSqlObject($table);
    }

    /**
     * @param string $tableName
     * @param string $oldName
     * @param string $newName
     * @param array $newDefinition
     */
    private static function _changeColumn($tableName, $oldName, $newName, array $newDefinition)
    {
        $table = new AlterTable($tableName);
        $column = self::_getInitColumn($newName, $newDefinition);
        $table->changeColumn($oldName, $column);
        self::_commit($table);
    }

    /**
     * @param string $name
     * @param array $definition
     * @return bool|\Zend\Db\Sql\Ddl\Column\ColumnInterface
     */
    private static function _getInitColumn($name, array $definition) {
        if (!isset($definition['type']) || is_int($name)) {
            // can't create column without type or name
            return false;
        }
        /** @var \Zend\Db\Sql\Ddl\Column\ColumnInterface $type */
        $type = '\\Zend\\Db\\Sql\\Ddl\\Column\\' . ucfirst($definition['type']);
        switch ($definition['type']) {
            case 'char':
            case 'varchar':
                /** @var Column\Varchar|Column\Char $type */
                $type = '\\Maketok\\Util\\Sql\\Ddl\\Column\\' . ucfirst($definition['type']);
                $nullable = isset($definition['nullable']) ? $definition['nullable'] : false;
                $default = isset($definition['default']) ? $definition['default'] : null;
                $length = isset($definition['length']) ? $definition['length'] : null;
                $column = new $type($name, $length, $nullable, $default);
                break;
            case 'bigInteger':
            case 'integer':
                /** @var Column\BigInteger|Column\Integer $type */
                $type = '\\Maketok\\Util\\Sql\\Ddl\\Column\\' . ucfirst($definition['type']);
                $nullable = isset($definition['nullable']) ? $definition['nullable'] : false;
                $default = isset($definition['default']) ? $definition['default'] : null;
                $options = array();
                if (isset($definition['length'])) {
                    $options['length'] = $definition['length'];
                }
                if (isset($definition['unsigned'])) {
                    $options['unsigned'] = $definition['unsigned'];
                }
                if (isset($definition['auto_increment'])) {
                    $options['auto_increment'] = $definition['auto_increment'];
                }
                $column = new $type($name, $nullable, $default, $options);
                break;
            case 'decimal':
            case 'float':
                $digits = isset($definition['digits']) ? $definition['digits'] : null;
                $decimal = isset($definition['decimal']) ? $definition['decimal'] : null;
                $options = array();
                if (isset($definition['unsigned'])) {
                    $options['unsigned'] = $definition['unsigned'];
                }
                $column = new Float($name, $digits, $decimal, $options);
                break;
            case 'blob':
                /** @var Blob $type */
                $nullable = isset($definition['nullable']) ? $definition['nullable'] : false;
                $column = new Blob($name, $nullable);
                break;
            case 'datetime':
                /** @var Datetime $type */
                $column = new Datetime($name);
                break;
            default:
                $column = new $type($name);
                break;
        }
        return $column;
    }

    /**
     * @param InstallerApplicableInterface $client
     */
    public function addClient($client)
    {
        $this->_clients[] = array(
            'name' => $client::getDdlConfigName(),
            'version' => $client::getDdlConfigVersion(),
            'config' => $client::getDdlConfig(),
        );
    }

    /**
     * @return \ArrayObject
     */
    public static function getDdlInstallerMap()
    {
        if (!isset(self::$_map)) {
            $data = self::_getLockStreamHandler()->read();
            if (empty($data)) {
                self::$_map = new \ArrayObject();
            } else {
                $_data = json_decode($data, true);
                self::$_map = new \ArrayObject($_data);
            }

        }
        return self::$_map;
    }

    /**
     * @return StreamHandler
     */
    private static function _getLockStreamHandler()
    {
        if (is_null(self::$_lockStreamHandler)) {
            $fullPath = APPLICATION_ROOT . DIRECTORY_SEPARATOR . 'var' . DIRECTORY_SEPARATOR . 'locks' . DIRECTORY_SEPARATOR . self::$_installerLockSheetName;
            self::$_lockStreamHandler = new StreamHandler();
            self::$_lockStreamHandler->setPath($fullPath);
            if (!file_exists($fullPath)) {
                // if file does not exist - create empty file
                self::$_lockStreamHandler->write('');
            }
        }
        return self::$_lockStreamHandler;
    }

    /**
     * @return bool
     */
    public function hasClients()
    {
        return (bool) count($this->_clients);
    }

    /**
     * @return void
     * @throws InstallerException
     */
    public function processClients()
    {
        foreach ($this->_clients as $_key => $_client) {
            $this->_processClient($_client);
            // remove client after processing
            unset($this->_clients[$_key]);
        }
        $result = self::_getLockStreamHandler()->writeWithLock(json_encode(self::$_map->getArrayCopy()));
        if ($result === false || $result === 0) {
            throw new InstallerException('Error writing lock data to installer map.');
        }
    }

    /**
     * @param array $client
     */
    protected function _processClient(array $client)
    {
        $_map = self::getDdlInstallerMap();
        if (isset($_map[$client['name']]) && is_array($_map[$client['name']])) {
            $clientConfig = $_map[$client['name']];
            // get the latest version
            // we need to account for the broken config order
            // get the max one
            uksort($clientConfig, array($this, '_natRecursiveCompare'));
            end($clientConfig);
            $lastKey = key($clientConfig);
            if ($this->_natRecursiveCompare($client['version'], $lastKey) === 1) {
                // proceed to compare schemas
                $this->_examineSchema($clientConfig[$lastKey], $client['config']);
            } elseif ($this->_natRecursiveCompare($client['version'], $lastKey) === -1) {
                // something is wrong with versioning
                // send notification
                $logger = Site::getServiceContainer()->get('logger');
                $logger->warning(
                    sprintf('The new version %s of DdlConfig %s is lower than the latest installed version %s',
                        $client['version'],
                        $client['name'],
                        $lastKey)
                );
            }
            // else config versions are identical - no need to do anything
        } else {
            $this->_examineSchema(array(), $client['config']);
        }
        $_map[$client['name']][$client['version']] = $client['config'];
    }

    /**
     * compare old and new schema and determine the course of the actions
     *
     * @param array $a oldConfig
     * @param array $b newConfig
     * @throws InstallerException
     */
    protected function _examineSchema(array $a, array $b)
    {
        if ($a === $b) {
            // if arrays are identical - do nothing
            return;
        }
        // actions
        $_dropTables = array();
        $_addTables = array();
        $_dropColumns = array();
        $_addColumns = array();
        $_changeColumns = array();
        $_dropConstraints = array();
        $_addConstraints = array();
        // tables
        foreach ($b as $tableName => $tableDefinition) {
            if (!array_key_exists($tableName, $a)) {
                $_addTables[] = [$tableName, $tableDefinition];
            } else {
                if (!isset($tableDefinition['columns']) || !is_array($tableDefinition['columns'])) {
                    throw new InstallerException(sprintf('Can not have a table `%s` without columns definition.', $tableName),
                        self::EXCEPTION_DDL_CODE);
                }
                $_newColumns = $tableDefinition['columns'];
                $_oldColumns = $a[$tableName]['columns'];
                $this->_intCompareColumns($_oldColumns, $_newColumns, $_dropColumns, $_addColumns, $_changeColumns, $tableName);
                $_oldConstraints = isset($a[$tableName]['constraints']) ? $a[$tableName]['constraints'] : array();
                $_newConstraints = isset($tableDefinition['constraints']) ? $tableDefinition['constraints'] : array();
                $this->_intCompareConstraints($_oldConstraints, $_newConstraints, $_dropConstraints, $_addConstraints, $tableName);
            }
        }
        foreach ($a as $tableName => $tableDefinition) {
            if (!array_key_exists($tableName, $b)) {
                $_dropTables[] = [$tableName];
            }
        }
        // action
        // order is important
        foreach ($_dropTables as $_definition) {
            self::_dropTable($_definition[0]);
        }
        foreach ($_addTables as $_definition) {
            self::_addTable($_definition[0], $_definition[1]);
        }
        // drop possible fk
        foreach ($_dropConstraints as $_definition) {
            self::_dropConstraint($_definition[0], $_definition[1]);
        }
        // add and change columns for indexes to process
        foreach ($_addColumns as $_definition) {
            self::_addColumn($_definition[0], $_definition[1], $_definition[2]);
        }
        foreach ($_changeColumns as $_definition) {
            self::_changeColumn($_definition[0], $_definition[1], $_definition[2], $_definition[3]);
        }
        // add constraints and indexes
        foreach ($_addConstraints as $_definition) {
            self::_addConstraint($_definition[0], $_definition[1], $_definition[2]);
        }
        // clear columns
        foreach ($_dropColumns as $_definition) {
            self::_dropColumn($_definition[0], $_definition[1]);
        }
    }

    /**
     * @param array $a
     * @param array $b
     * @param array $_dropColumns
     * @param array $_addColumns
     * @param array $_changeColumns
     * @param string $tableName
     * @return array
     */
    protected function _intCompareColumns(array $a, array $b, &$_dropColumns, &$_addColumns, &$_changeColumns, $tableName)
    {
        foreach ($b as $columnName => $columnDefinition) {
            if (!array_key_exists($columnName, $a) && !isset($columnDefinition['old_name'])) {
                $_addColumns[] = [$tableName, $columnName, $columnDefinition];
            } elseif (isset($columnDefinition['old_name']) && is_string($columnDefinition['old_name'])) {
                $_changeColumns[] = [$tableName, $columnDefinition['old_name'],  $columnName, $columnDefinition];
            } elseif ($columnDefinition === $a[$columnName]) {
                continue;
            } else {
                $_changeColumns[] = [$tableName, $columnName,  $columnName, $columnDefinition];
            }
        }
        foreach ($a as $columnName => $columnDefinition) {
            if (!array_key_exists($columnName, $b)) {
                $_dropColumns[] = [$tableName, $columnName];
            }
        }
    }

    /**
     * @param array $a
     * @param array $b
     * @param array $_dropConstraints
     * @param array $_addConstraints
     * @param string $tableName
     * @return array
     */
    protected function _intCompareConstraints(array $a, array $b, &$_dropConstraints, &$_addConstraints, $tableName)
    {
        foreach ($b as $constraintName => $constraintDefinition) {
            if (!array_key_exists($constraintName, $a)) {
                $_addConstraints[] = [$tableName, $constraintName, $constraintDefinition];
            }
        }
        foreach ($a as $constraintName => $constraintDefinition) {
            if (!array_key_exists($constraintName, $b)) {
                $_dropConstraints[] = [$tableName, $constraintName];
            }
        }
    }

    /**
     * the recursive compare function
     * should compare versions
     * @param $a
     * @param $b
     * @return int
     */
    protected function _natRecursiveCompare($a, $b)
    {
        $aA = explode('.', $a);
        $aB = explode('.', $b);
        if (count($aA) > count($aB)) {
            for ($i = count($aB); $i < count($aA); $i++){
                $aB[] = 0;
            }
        } elseif(count($aB) > count($aA)) {
            for ($i = count($aA); $i < count($aB); $i++){
                $aA[] = 0;
            }
        }
        // cast all versions to int
        foreach ($aA as &$v) {$v = (int) $v;}
        foreach ($aB as &$v) {$v = (int) $v;}
        for ($i = 0; $i < count($aA); $i++) {
            if ($aA[$i] > $aB[$i]) {
                return 1;
            } elseif ($aB[$i] > $aA[$i]) {
                return -1;
            } elseif ($aA[$i] === $aB[$i]) {
                continue;
            }
        }
        // versions are identical
        return 0;
    }

    /**
     * @param string $name lock file name
     * @return $this
     */
    public function setInstallerLockName($name)
    {
        self::$_installerLockSheetName = $name;
        return $this;
    }
}