<?php
/**
 * This is a part of Maketok Site. Licensed under GPL 3.0
 * @project site
 * @developer Oleg Kulik slayer.birden@gmail.com maketok.com
 */

namespace Maketok\Module\Mvc;

/**
 * @codeCoverageIgnore
 */
trait ControllerTemplateTrait
{

    /**
     * magic
     * @param string $suffix
     */
    public function loadModulePath($suffix = '')
    {
        $ns = explode('\\', get_class($this));
        if (($i = array_search('modules', $ns)) !== false && isset($ns[$i+1])) {
            $this->addTemplatePath(AR. '/modules/' . $ns[$i+1] . '/view/' . $suffix);
        }
    }

    /**
     * @param $path
     * @return self
     */
    abstract function addTemplatePath($path);
}
