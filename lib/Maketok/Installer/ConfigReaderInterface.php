<?php
/**
 * This is a part of Maketok Site. Licensed under GPL 3.0
 * Please do not use for your own profit.
 * @project site
 * @developer Slayer slayer.birden@gmail.com maketok.com
 */

namespace Maketok\Installer;

interface ConfigReaderInterface
{

    /**
     * @param array $configChain
     * @return void
     */
    public function processConfig(array $configChain);

    /**
     * @return void
     */
    public function validateDirectives();

    /**
     * @return void
     */
    public function compileDirectives();

    /**
     * @return array
     */
    public function getDirectives();
}
