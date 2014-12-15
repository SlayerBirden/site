<?php
/**
 * This is a part of Maketok Site. Licensed under GPL 3.0
 * @project site
 * @developer Oleg Kulik slayer.birden@gmail.com maketok.com
 */

namespace Maketok\Module\Mvc;

interface ModulesAwareControllerInterface
{

    /**
     * add path for given module
     * @param string $suffix
     */
    public function loadModulePath($suffix = '');
}
