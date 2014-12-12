<?php
/**
 * This is a part of Maketok Site. Licensed under GPL 3.0
 * @project site
 * @developer Oleg Kulik slayer.birden@gmail.com maketok.com
 */

namespace Maketok\Installer\Ddl;

use Maketok\Installer\DirectivesInterface;

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
     * {@inheritdoc}
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->asArray());
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
     * {@inheritdoc}
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
        // order is important
        // drop tables before adding
        // add/change columns before adding constraints
        // drop constraints before dropping columns
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

    /**
     * @return mixed
     */
    public function unique()
    {
        foreach ($this as &$type) {
            $type = $this->arrayUnique($type);
        }
    }

    /**
     * @param array $a
     * @return array
     */
    private function arrayUnique(array $a)
    {
        // kind of a hack to make it multi-dimensional
        return array_unique($a, SORT_REGULAR);
    }

}
