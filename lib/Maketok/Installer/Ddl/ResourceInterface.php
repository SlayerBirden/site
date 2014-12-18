<?php
/**
 * This is a part of Maketok site package.
 *
 * @author Oleg Kulik <slayer.birden@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Maketok\Installer\Ddl;

use Maketok\Installer\ResourceInterface as BaseResource;

interface ResourceInterface extends BaseResource
{

    /**
     * get table description
     *
     * @param string $table
     * @return array
     */
    public function getTable($table);

    /**
     * get column description
     *
     * @param string $table
     * @param string $column
     * @return array
     */
    public function getColumn($table, $column);

    /**
     * get constraint description
     *
     * @param string $table
     * @param string $constraint
     * @return array
     */
    public function getConstraint($table, $constraint);

    /**
     * get index description
     *
     * @param string $table
     * @param string $index
     * @return array
     */
    public function getIndex($table, $index);
}
