<?php
/**
 * This is a part of Maketok Site. Licensed under GPL 3.0
 * Please do not use for your own profit.
 * @project store
 * @developer Slayer slayer.birden@gmail.com maketok.com
 */
namespace Maketok\App\Ddl;

use Maketok\Util\StreamHandler;
use Monolog\Logger;
use SebastianBergmann\Exporter\Exception;
use Zend\Db\Sql\Ddl\Column;
use Zend\Db\Sql\Ddl\Constraint;
use Zend\Db\Sql\Ddl\CreateTable;
use Zend\Db\Sql\Ddl\AlterTable;
use Zend\Db\Sql\Ddl\DropTable;

class Installer
{

    const EXCEPTION_DDL_CODE = 300;

    private static $_installerLockSheetPath = 'var/locks/ddl_installer.lock';
    private static $_lockStreamHandler;
    private $_clients = array();
    private static $_map;
    private $_loggerName = 'ddl_installer';

    static $_availableConstraintTypes = ['primaryKey', 'uniqueKey', 'foreignKey'];

    /**
     * @param string $tableName
     * @param array $tableDefinition
     * @throws Exception
     */
    private static function _addTable($tableName, array $tableDefinition)
    {
        $table = new CreateTable($tableName);
        if (!isset($tableDefinition['columns']) || !is_array($tableDefinition['columns'])) {
            throw new Exception(sprintf('Can not create a table `%s` without columns definition.', $tableName),
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
    }

    /**
     * @param string $tableName
     * @param string $columnName
     * @param array $columnDefinition
     * @param null|CreateTable|AlterTable $table
     */
    private static function _addColumn($tableName, $columnName, array $columnDefinition, $table = null)
    {
        if (is_null($table)) {
            $table = new AlterTable($tableName);
        }
        if (!isset($columnDefinition['type']) || is_int($columnName)) {
            // can't create column without type or name
            return;
        }
        /** @var Column\ColumnInterface $type */
        $type = 'Column\\' . ucfirst($columnDefinition['type']);
        $length = isset($columnDefinition['length']) ? $columnDefinition['length'] : null;
        $column = new $type($columnName, $length);
        $table->addColumn($column);
    }

    /**
     * @param string $tableName
     * @param string $constraintName
     * @param array $constraintDefinition
     * @param null|CreateTable|AlterTable $table
     * @throws Exception
     */
    private static function _addConstraint($tableName, $constraintName, array $constraintDefinition, $table = null)
    {
        if (is_null($table)) {
            $table = new AlterTable($tableName);
        }
        if (!isset($constraintDefinition['type']) || !in_array($constraintDefinition['type'], self::$_availableConstraintTypes)) {
            // can't create constraint or unavailable constraint type
            throw new Exception(sprintf('Can not create constraint %s for table %s. Missing or unavailable type.', $constraintName, $tableName));
        }
        /** @var Constraint\ConstraintInterface $type */
        $type = 'Constraint\\' . ucfirst($constraintDefinition['type']);

        $constraint = new $type($constraintDefinition['def'], $constraintName);
        $table->addConstraint($constraint);
    }

    /**
     * @param string $tableName
     */
    private static function _dropTable($tableName)
    {
        new DropTable($tableName);
    }

    /**
     * @param string $tableName
     * @param string $columnName
     */
    private static function _dropColumn($tableName, $columnName)
    {
        $table = new AlterTable($tableName);
        $table->dropColumn($columnName);
    }

    /**
     * @param string $tableName
     * @param string $constraintName
     */
    private static function _dropConstraint($tableName, $constraintName)
    {
        $table = new AlterTable($tableName);
        $table->dropConstraint($constraintName);
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
        if (!isset($newDefinition['type']) || is_int($newName)) {
            // can't create column without type or name
            return;
        }
        /** @var Column\ColumnInterface $type */
        $type = 'Column\\' . ucfirst($newDefinition['type']);
        $length = isset($newDefinition['length']) ? $newDefinition['length'] : null;
        $column = new $type($newName, $length);
        $table->changeColumn($oldName, $column);
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
     * @return array
     */
    public static function getDdlInstallerMap()
    {
        if (!isset(self::$_map)) {
            $data = self::_getLockStreamHandler()->read();
            if (empty($data)) {
                self::$_map = array();
            }
            self::$_map = json_decode($data, true);
        }
        return self::$_map;
    }

    /**
     * @return StreamHandler
     */
    private static function _getLockStreamHandler()
    {
        if (is_null(self::$_lockStreamHandler)) {
            $fullPath = APPLICATION_ROOT . DIRECTORY_SEPARATOR . self::$_installerLockSheetPath;
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
     */
    public function processClients()
    {
        foreach ($this->_clients as $_client) {
            $this->_processClient($_client);
        }
    }

    protected function _processClient(array $client)
    {
        $_map = self::getDdlInstallerMap();
        if (isset($_map[$client['name']]) && is_array($_map[$client['name']])) {
            $clientConfig = $_map[$client['name']];
            //get the latest version
            end($clientConfig);
            $lastKey = key($clientConfig);
            if ($this->_natRecursiveCompare($client['version'], $lastKey) === 1) {
                // proceed to compare schemas

            } elseif ($this->_natRecursiveCompare($client['version'], $lastKey) === -1) {
                // something is wrong with versioning
                // send notification
                $logger = new Logger($this->_loggerName);
                $logPath = APPLICATION_ROOT . DIRECTORY_SEPARATOR . 'var' . DIRECTORY_SEPARATOR . 'log' . DIRECTORY_SEPARATOR . $this->_loggerName . '.log';
                $logger->pushHandler(new \Monolog\Handler\StreamHandler($logPath), Logger::WARNING);
                $logger->addWarning(
                    sprintf('The new version %s of DdlConfig %s is lower than the latest installed version %s',
                        $client['version'],
                        $client['name'],
                        $lastKey)
                );
            }
            // else config versions are identical - no need to do anything
        }
    }

    /**
     * compare old and new schema and determine the course of the actions
     *
     * @param array $a oldConfig
     * @param array $b newConfig
     * @throws Exception
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
                    throw new Exception(sprintf('Can not have a table `%s` without columns definition.', $tableName),
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
        foreach ($_dropTables as $_definition) {
            self::_dropTable($_definition[0]);
        }
        foreach ($_addTables as $_definition) {
            self::_addTable($_definition[0], $_definition[1]);
        }
        foreach ($_dropColumns as $_definition) {
            self::_dropColumn($_definition[0], $_definition[1]);
        }
        foreach ($_addColumns as $_definition) {
            self::_addColumn($_definition[0], $_definition[1], $_definition[2]);
        }
        foreach ($_changeColumns as $_definition) {
            self::_changeColumn($_definition[0], $_definition[1], $_definition[2], $_definition[3]);
        }
        foreach ($_dropConstraints as $_definition) {
            self::_dropConstraint($_definition[0], $_definition[1]);
        }
        foreach ($_addConstraints as $_definition) {
            self::_addConstraint($_definition[0], $_definition[1], $_definition[2]);
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
}