<?php
/**
 * This is a part of Maketok Site. Licensed under GPL 3.0
 * Please do not use for your own profit.
 * @project site
 * @developer Slayer slayer.birden@gmail.com maketok.com
 */

namespace Maketok\Installer\Data;

use Maketok\Installer\ConfigReaderInterface;

class ConfigReader implements ConfigReaderInterface
{

    /**
     * @param array $configChain
     * @return void
     */
    public function processConfig(array $configChain)
    {
        // TODO: Implement processConfig() method.
    }

    /**
     * @return void
     */
    public function validateDirectives()
    {
        // TODO: Implement validateDirectives() method.
    }

    /**
     * @return array
     */
    public function getDirectives()
    {
        // TODO: Implement getDirectives() method.
    }

    /**
     * @return void
     */
    public function compileDirectives()
    {
        // TODO: Implement compileDirectives() method.
    }

    /**
     * create config tree for all clients
     *
     * @param array|\ArrayObject $clients
     * @return void
     */
    public function buildDependencyTree($clients)
    {
        // TODO: Implement buildDependencyTree() method.
    }

    /**
     * validate tree
     *
     * @return void
     */
    public function validateDependencyTree()
    {
        // TODO: Implement validateDependencyTree() method.
    }

    /**
     * compile tree, merge all branches into main branch
     *
     * @return void
     */
    public function mergeDependencyTree()
    {
        // TODO: Implement mergeDependencyTree() method.
    }

    /**
     * returns the directives
     *
     * @return array
     */
    public function getDependencyTree()
    {
        // TODO: Implement getDependencyTree() method.
    }

    /**
     * return merged config
     *
     * @return array
     */
    public function getMergedConfig()
    {
        // TODO: Implement getMergedConfig() method.
    }
}
