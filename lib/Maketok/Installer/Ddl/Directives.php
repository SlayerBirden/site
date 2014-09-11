<?php
/**
 * This is a part of Maketok Site. Licensed under GPL 3.0
 * Please do not use for your own profit.
 * @project site
 * @developer Slayer slayer.birden@gmail.com maketok.com
 */

namespace Maketok\Installer\Ddl;

use Traversable;

class Directives implements \IteratorAggregate, \Countable
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
        return new \ArrayIterator(array(
            'addTables' => $this->addTables,
            'dropTables' => $this->dropTables,
            'addColumns' => $this->addColumns,
            'changeColumns' => $this->changeColumns,
            'dropColumns' => $this->dropColumns,
            'addConstraints' => $this->addConstraints,
            'dropConstraints' => $this->dropConstraints,
            'addIndices' => $this->addIndices,
            'dropIndices' => $this->dropIndices,
        ));
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
}
