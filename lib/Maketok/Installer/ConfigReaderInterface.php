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
     * create directives out of config chain
     *
     * @param array $configChain
     * @return void
     */
    public function processConfig(array $configChain);

    /**
     * validate directives:
     *
     * @return void
     */
    public function validateDirectives();

    /**
     * compile directives, so all opposites are removed and updates stacked
     *
     * @return void
     */
    public function compileDirectives();

    /**
     * returns the directives
     *
     * @return array
     */
    public function getDirectives();
}
