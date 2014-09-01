<?php
/**
 * This is a part of Maketok Site. Licensed under GPL 3.0
 * Please do not use for your own profit.
 * @project site
 * @developer Slayer slayer.birden@gmail.com maketok.com
 */

namespace Maketok\Mvc\Controller;

use Maketok\Mvc\GenericException;
use Maketok\Template;

class AbstractAdminController extends AbstractController
{

    /**
     * @param null $template
     * @param string|null $module
     * @throws GenericException
     * @return string
     */
    protected function _getTemplatePath($template = null, $module = null)
    {
        if (is_null($module)) {
            $module = $this->_module;
        }
        if (is_null($template)) {
            $template = $this->_template;
        }
        if (is_null($this->_template)) {
            throw new GenericException("Can't find template path, no template set.");
        }
        return AR . DS . 'modules' . DS . $module . DS . 'view' . DS . 'admin' . DS . $template;
    }
}
