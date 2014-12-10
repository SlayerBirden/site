<?php
/**
 * This is a part of Maketok Site. Licensed under GPL 3.0
 *
 * @project site
 * @developer Oleg Kulik slayer.birden@gmail.com maketok.com
 */

namespace Maketok\Installer\Ddl;

use Maketok\Installer\ConfigReaderInterface as BaseConfigReader;

interface ConfigReaderInterface extends BaseConfigReader
{

    /**
     * create config tree for all clients
     *
     * @param array|\ArrayObject $clients
     * @return void
     */
    public function buildDependencyTree($clients);

    /**
     * validate tree
     *
     * @return void
     */
    public function validateDependencyTree();

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
