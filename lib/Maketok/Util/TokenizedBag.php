<?php
/**
 * This is a part of Maketok site package.
 *
 * @author Oleg Kulik <slayer.birden@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
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
