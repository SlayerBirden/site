<?php
/**
 * This is a part of Maketok site package.
 *
 * @author Oleg Kulik <slayer.birden@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Maketok\Installer\Ddl\Mysql;

use Maketok\Installer\Ddl\Mysql\Parser\ParserInterface;
use Maketok\Installer\Ddl\Mysql\Parser\Table;
use Maketok\Installer\Ddl\Mysql\Procedure\ProcedureInterface;
use Maketok\Installer\Ddl\ResourceInterface;
use Maketok\Installer\DirectivesInterface;
use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\Sql;

class Resource implements ResourceInterface
{
    /** @var \Zend\Db\Adapter\Adapter  */
    protected $_adapter;

    /**
     * @var \Zend\Db\Sql\Sql
     */
    private $sql;
    /** @var array|\Iterator */
    private $_procedures;

    public function __construct(Adapter $adapter, Sql $sql)
    {
        $this->_adapter = $adapter;
        $this->sql = $sql;
    }

    /**
     * {@inheritdoc}
     */
    public function getTable($table)
    {
        $data = $this->getTableArray($table);
        $parser = new Table($data);

        return $parser->parse();
    }

    /**
     * @param  string $table
     * @return array
     */
    protected function getTableArray($table)
    {
        try {
            $result = $this->_adapter->query(
                "SHOW CREATE TABLE `$table`", Adapter::QUERY_MODE_EXECUTE);
            $data = $result->current();
            $data = $data->getArrayCopy();
            $data = explode("\n", $data['Create Table']);
        } catch (\Exception $e) {
            // this is case with un-existing table
            $data = [];
        }

        return $data;
    }

    /**
     * @param  string $table
     * @return array
     */
    protected function getStrippedTableArray($table)
    {
        $data = $this->getTableArray($table);
        array_shift($data);
        array_pop($data);

        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function getColumn($table, $column)
    {
        return $this->getTablePart('column', $table, $column);
    }

    /**
     * {@inheritdoc}
     */
    public function getConstraint($table, $constraint)
    {
        return $this->getTablePart('constraint', $table, $constraint);
    }

    /**
     * @param  string $type
     * @param  string $table
     * @param  string $part
     * @return array
     */
    public function getTablePart($type, $table, $part)
    {
        $data = $this->getStrippedTableArray($table);
        foreach ($data as $row) {
            /** @var ParserInterface $parser */
            $parserClass = $this->getParserClass($type);
            $parser = new $parserClass($row, $part);
            $res = $parser->parse();
            if (!empty($res)) {
                return $res;
            }
        }

        return [];
    }

    /**
     * @param  string $type
     * @return string
     */
    public function getParserClass($type)
    {
        return 'Maketok\Installer\Ddl\Mysql\Parser\\' . ucfirst($type);
    }

    /**
     * {@inheritdoc}
     */
    public function getIndex($table, $index)
    {
        return $this->getTablePart('index', $table, $index);
    }

    /**
     * {@inheritdoc}
     * @throws \LogicException
     * @throws \InvalidArgumentException
     */
    public function createProcedures(DirectivesInterface $directives)
    {
        if (isset($this->_procedures)) {
            throw new \LogicException("Wrong context of launching create procedures method. The procedures are already created.");
        }
        $this->_procedures = [];
        foreach ($directives as $type => $list) {
            $className = $this->getProcedureClassNameFromType($type);
            /** @var ProcedureInterface $procedureClass */
            $procedureClass = new $className($this->sql);
            foreach ($list as $item) {
                $this->_procedures[] = $procedureClass->getQuery($item);
            }
        }
    }

    /**
     * @param $type
     * @return string
     */
    public function getProcedureClassNameFromType($type)
    {
        $prefix = 'Maketok\Installer\Ddl\Mysql\Procedure\\';
        if ($type == 'addIndices') {
            $base = 'AddConstraint';
        } elseif ($type == 'dropIndices') {
            $base = 'DropConstraint';
        } else {
            // ucfirst + strip last "s"
            $base = ucfirst(substr($type, 0, strlen($type) - 1));
        }

        return $prefix . $base;
    }

    /**
     * {@inheritdoc}
     * @throws \LogicException
     */
    public function runProcedures()
    {
        if (is_null($this->_procedures)) {
            throw new \LogicException("Wrong context of using runProcedures. Procedures are not created yet.");
        }
        if (!(is_array($this->_procedures) ||
            (is_object($this->_procedures) && $this->_procedures instanceof \Iterator))) {
            throw new \LogicException("Unknown type of procedures.");
        }
        foreach ($this->_procedures as $query) {
            $this->commit($query);
        }
    }

    /**
     * @param string $query
     */
    private function commit($query)
    {
        $adapter = $this->_adapter;
        $this->_adapter->query(
            $query,
            $adapter::QUERY_MODE_EXECUTE
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getProcedures()
    {
        return $this->_procedures;
    }
}
