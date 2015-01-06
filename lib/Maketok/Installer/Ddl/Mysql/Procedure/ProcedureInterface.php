<?php
/**
 * This is a part of Maketok site package.
 *
 * @author Oleg Kulik <slayer.birden@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Maketok\Installer\Ddl\Mysql\Procedure;

use Maketok\Installer\Ddl\Mysql\Resource;
use Zend\Db\Sql\Sql;

interface ProcedureInterface
{
    /**
     * set sql
     * @param Sql $sql
     */
    public function __construct(Sql $sql, Resource $resource);

    /**
     * get Query
     * @param  array  $args
     * @return string
     */
    public function getQuery(array $args);

    /**
     * get signature for query
     * @param  array  $args
     * @return string
     */
    public function getQuerySignature(array $args);
}
