<?php
/**
 * This is a part of Maketok Site. Licensed under GPL 3.0
 * Please do not use for your own profit.
 * @project site
 * @developer Slayer slayer.birden@gmail.com maketok.com
 */

namespace Resource\admin\controller;

use Maketok\Mvc\Controller\AbstractController as BaseAbstractController;

class AbstractController extends BaseAbstractController
{

    /**
     * {@inheritdoc}
     */
    protected function _getTemplatePath($template = null, $module = null)
    {
        if (is_null($template)) {
            $template = $this->_template;
        }
        return AR . "/Resource/admin/view/$template";
    }
} 
