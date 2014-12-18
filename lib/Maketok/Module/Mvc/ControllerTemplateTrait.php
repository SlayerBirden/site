<?php
/**
 * This is a part of Maketok site package.
 *
 * @author Oleg Kulik <slayer.birden@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
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
     * @param  string $path
     * @return self
     */
    abstract public function addTemplatePath($path);
}
