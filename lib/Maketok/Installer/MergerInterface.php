<?php
/**
 * This is a part of Maketok Site. Licensed under GPL 3.0
 * Please do not use for your own profit.
 * @project site
 * @developer Slayer slayer.birden@gmail.com maketok.com
 */

namespace Maketok\Installer;


interface MergerInterface
{

    /**
     * Merge arbitrary number of configs
     * Merger assumes that top level keys of config array are client names
     *
     * @internal param $ [array $a1 [, array $a2 [, array $a3]]]
     * @return array
     */
    public function merge();

    /**
     * @param string $key
     * @return array
     */
    public function unMerge($key);

    /**
     * @return bool
     */
    public function hasConflicts();

    /**
     * @return array
     */
    public function getConflictedKeys();

    /**
     * @return array
     */
    public function getSharedKeys();
}
