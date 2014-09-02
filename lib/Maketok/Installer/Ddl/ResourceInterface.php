<?php
/**
 * This is a part of Maketok Site. Licensed under GPL 3.0
 * Please do not use for your own profit.
 * @project site
 * @developer Slayer slayer.birden@gmail.com maketok.com
 */

namespace Maketok\Installer\Ddl;

interface ResourceInterface
{

    /**
     * @param string $table
     * @return array
     */
    public function getTable($table);

    /**
     * @param string $table
     * @param string $column
     * @return array
     */
    public function getColumn($table, $column);

    /**
     * @param string $table
     * @param string $constraint
     * @return array
     */
    public function getConstraint($table, $constraint);

    /**
     * @param string $table
     * @param string $index
     * @return array
     */
    public function getIndex($table, $index);
}