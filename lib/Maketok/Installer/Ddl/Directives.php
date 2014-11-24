<?php
/**
 * This is a part of Maketok Site. Licensed under GPL 3.0
 * Please do not use for your own profit.
 * @project site
 * @developer Slayer slayer.birden@gmail.com maketok.com
 */

namespace Maketok\Installer\Ddl;

use Maketok\Installer\DirectivesInterface;
use Traversable;

class Directives implements DirectivesInterface
{

    /** @var array */
    public $addTables = [];
    /** @var array */
    public $dropTables = [];
    /** @var array */
    public $addColumns = [];
    /** @var array */
    public $changeColumns = [];
    /** @var array */
    public $dropColumns = [];
    /** @var array */
    public $addConstraints = [];
    /** @var array */
    public $dropConstraints = [];
    /** @var array */
    public $addIndices = [];
    /** @var array */
    public $dropIndices = [];

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Retrieve an external iterator
     * @link http://php.net/manual/en/iteratoraggregate.getiterator.php
     * @return Traversable An instance of an object implementing <b>Iterator</b> or
     * <b>Traversable</b>
     */
    public function getIterator()
    {
        // order is important
        // drop tables before adding
        // add/change columns before adding constraints
        // drop constraints before dropping columns
        return new \ArrayIterator(array(
            'dropTables' => $this->dropTables,
            'addTables' => $this->addTables,
            'dropConstraints' => $this->dropConstraints,
            'dropIndices' => $this->dropIndices,
            'dropColumns' => $this->dropColumns,
            'addColumns' => $this->addColumns,
            'changeColumns' => $this->changeColumns,
            'addConstraints' => $this->addConstraints,
            'addIndices' => $this->addIndices,
        ));
    }

    /**
     * @param string $key
     * @param mixed $def
     * @throws \InvalidArgumentException
     */
    public function addProp($key, $def)
    {
        if (!property_exists($this, $key)) {
            throw new \InvalidArgumentException(sprintf("The property %s does not exist.", $key));
        }
        array_push($this->$key, $def);
    }

    /**
     * (PHP 5 &gt;= 5.1.0)<br/>
     * Count elements of an object
     * @link http://php.net/manual/en/countable.count.php
     * @return int The custom count as an integer.
     * </p>
     * <p>
     * The return value is cast to an integer.
     */
    public function count()
    {
        $count = 0;
        foreach ($this as $array) {
            $count += count($array);
        }
        return $count;
    }

    /**
     * @param string $key
     * @return bool
     */
    public function has($key)
    {
        if (isset($this->$key)) {
            return count($this->$key) > 0;
        }
        return false;
    }

    /**
     * @return array
     */
    public function asArray()
    {
        return [
            'dropTables' => $this->dropTables,
            'addTables' => $this->addTables,
            'dropConstraints' => $this->dropConstraints,
            'dropIndices' => $this->dropIndices,
            'dropColumns' => $this->dropColumns,
            'addColumns' => $this->addColumns,
            'changeColumns' => $this->changeColumns,
            'addConstraints' => $this->addConstraints,
            'addIndices' => $this->addIndices,
        ];
    }
}
