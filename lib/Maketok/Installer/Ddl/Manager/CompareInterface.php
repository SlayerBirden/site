<?php
/**
 * This is a part of Maketok site package.
 *
 * @author Oleg Kulik <slayer.birden@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Maketok\Installer\Ddl\Manager;

use Maketok\Installer\Ddl\Directives;

interface CompareInterface
{
    /**
     * @param  array      $a
     * @param  array      $b
     * @param $tableName
     * @param  Directives $directives
     * @return void
     */
    public function intlCompare(array $a, array $b, $tableName, Directives $directives);
}
