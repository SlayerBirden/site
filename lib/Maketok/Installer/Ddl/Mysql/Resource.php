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
use Maketok\Installer\Exception;
use Maketok\Util\ArrayValueTrait;
use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\Sql;

class Resource implements ResourceInterface
{
    use ArrayValueTrait;
    /**
     * @var \Zend\Db\Adapter\Adapter
     */
    protected $adapter;
    /**
     * @var \Zend\Db\Sql\Sql
     */
    protected $sql;
    /**
     * @var array|\Iterator
     */
    protected $procedures;

    /**
     * @param Adapter $adapter
     * @param Sql $sql
     */
    public function __construct(Adapter $adapter, Sql $sql)
    {
        $this->adapter = $adapter;
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
            $result = $this->adapter->query(
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
        if (isset($this->procedures)) {
            throw new \LogicException("Wrong context of launching create procedures method. The procedures are already created.");
        }
        $this->procedures = [];
        foreach ($directives as $type => $list) {
            $className = $this->getProcedureClassNameFromType($type);
            /** @var ProcedureInterface $procedureClass */
            $procedureClass = new $className($this->sql);
            foreach ($list as $item) {
                $this->procedures[] = $procedureClass->getQuery($item);
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
        if (is_null($this->procedures)) {
            throw new \LogicException("Wrong context of using runProcedures. Procedures are not created yet.");
        }
        if (!(is_array($this->procedures) ||
            (is_object($this->procedures) && $this->procedures instanceof \Iterator))) {
            throw new \LogicException("Unknown type of procedures.");
        }
        foreach ($this->procedures as $query) {
            $this->commit($query);
        }
    }

    /**
     * @param string $query
     */
    private function commit($query)
    {
        $adapter = $this->adapter;
        $this->adapter->query(
            $query,
            $adapter::QUERY_MODE_EXECUTE
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getProcedures()
    {
        return $this->procedures;
    }

    /**
     * {@inheritdoc}
     * @throws \Maketok\Installer\Exception
     */
    public function processValidateMergedConfig(array &$config)
    {
        foreach ($config as $tableName => $definition) {
            $constraints = $this->getIfExists('constraints', $definition, []);
            $indices = $this->getIfExists('indices', $definition, []);
            $fkMap = $this->getKeyMap($constraints, 'type', ['foreignKey']);
            if (count($fkMap)) {
                // create index map
                $indexMap = $this->getKeyMap($indices, null, [], 'definition', [$this, 'getFirstEl']);
                $indexFromConstraint = $this->getKeyMap($constraints, 'type', ['uniqueKey', 'primaryKey'], 'definition', [$this, 'getFirstEl']);
                $indexMap = array_replace($indexMap, $indexFromConstraint);
                // check correspondence
                foreach ($fkMap as $column => $name) {
                    if (!isset($indexMap[$column])) {
                        $config[$tableName]['indices'][$name] = [
                            'type' => 'index',
                            'definition' => [$column],
                        ];
                    }
                }
            }
        }
    }

    /**
     * @param mixed $value
     * @return string
     */
    protected function getFirstEl($value)
    {
        if (is_array($value)) {
            return current($value);
        }
        return $value;
    }

    /**
     * @param array $config
     * @param string $matchKey
     * @param string[] $matchedValues
     * @param string $columnKey
     * @param callable $columnGetStrat
     * @return \string[]
     */
    protected function getKeyMap(array $config, $matchKey, array $matchedValues, $columnKey = 'column', callable $columnGetStrat = null)
    {
        $map = [];
        foreach ($config as $name => $def) {
            if (!is_null($matchKey)) {
                $key = $this->getIfExists($matchKey, $def, function () use ($matchKey) {
                    throw new Exception(sprintf("Can not get key to match against - '%s'.", $matchKey));
                });
                if (!in_array($key, $matchedValues)) {
                    continue;
                }
            }
            $column = $this->getIfExists($columnKey, $def, function () use ($columnKey) {
                throw new Exception(sprintf("Definition doesn't have '%s' - column key.", $columnKey));
            });
            if (!is_null($columnGetStrat)) {
                $column = call_user_func($columnGetStrat, $column);
            }
            $map[$column] = $name;
        }
        return $map;
    }
}
