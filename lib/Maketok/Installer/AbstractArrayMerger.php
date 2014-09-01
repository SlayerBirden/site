<?php
/**
 * This is a part of Maketok Site. Licensed under GPL 3.0
 * Please do not use for your own profit.
 * @project site
 * @developer Slayer slayer.birden@gmail.com maketok.com
 */

namespace Maketok\Installer;

abstract class AbstractArrayMerger
{

    /** @var  array */
    protected $_mergedConfig;
    /** @var array  */
    protected $_simpleKeys;
    /** @var bool  */
    protected $_hasConflicts;
    /** @var array  */
    protected $_conflictedKeys;
    /** @var array  */
    protected $_sharedKeys;

    /**
     * Compares 2 low level config entries
     *
     * @param mixed $el1
     * @param mixed $el2
     * @throws MergerException
     * @return bool
     */
    public static  function configElementArrayCompare($el1, $el2)
    {
        // this is tricky
        // we want to consider addition as a valid comparison
        if (is_null($el1) || is_null($el2)) {
            return true;
        }
        if (gettype($el1) != gettype($el2)) {
            throw new MergerException("Can not compare elements of different types.");
        }
        switch (gettype($el1)) {
            case 'string':
                return strcmp($el1, $el2) == 0;
            case 'object':
                throw new MergerException("Don't know how to compare objects.");
            case 'array':
                $res = true;
                foreach ($el1 as $key => $val) {
                    if (isset($el2[$key])) {
                        $res = $res && self::configElementArrayCompare($val, $el2[$key]);
                    }
                }
                return $res;
            default:
                return $el1 === $el2;
        }
    }

}
