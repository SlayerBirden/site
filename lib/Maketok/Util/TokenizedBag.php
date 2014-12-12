<?php
/**
 * This is a part of Maketok Site. Licensed under GPL 3.0
 * @project site
 * @developer Oleg Kulik slayer.birden@gmail.com maketok.com
 */

namespace Maketok\Util;

class TokenizedBag implements \IteratorAggregate, \Countable
{

    /**
     * @var TokenizedBagPart[]
     */
    protected $parts = [];

    /**
     * {@inheritdoc}
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->parts);
    }

    /**
     * {@inheritdoc}
     */
    public function count()
    {
        return count($this->parts);
    }

    /**
     * @param TokenizedBagPart $part
     * @return $this
     */
    public function addPart(TokenizedBagPart $part)
    {
        $this->parts[] = $part;
        return $this;
    }

    /**
     * @return array
     */
    public function asArray()
    {
        $arrayParts = [];
        /** @var TokenizedBagPart[] $this */
        foreach ($this as $part) {
            $arrayParts[] = $part->toArray();
        }
        return $arrayParts;
    }
}
