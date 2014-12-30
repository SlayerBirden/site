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

use Maketok\Installer\ConfigReaderInterface as BaseConfigReader;

interface ConfigReaderInterface extends BaseConfigReader
{
    /**
     * create config tree for all clients
     *
     * @param  array|\ArrayObject|\Maketok\Installer\Ddl\Resource\Model\DdlClient[] $clients
     * @return void
     */
    public function buildDependencyTree($clients);

    /**
     * compile tree, merge all branches into main branch
     *
     * @return void
     */
    public function mergeDependencyTree();

    /**
     * returns the directives
     *
     * @return array
     */
    public function getDependencyTree();
}
