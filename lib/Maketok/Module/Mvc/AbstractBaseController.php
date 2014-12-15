<?php
/**
 * This is a part of Maketok Site. Licensed under GPL 3.0
 * @project site
 * @developer Oleg Kulik slayer.birden@gmail.com maketok.com
 */

namespace Maketok\Module\Mvc;

use \Maketok\Mvc\Controller\AbstractBaseController as BaseAbsractBaseController;

/**
 * @codeCoverageIgnore
 */
class AbstractBaseController extends BaseAbsractBaseController implements ModulesAwareControllerInterface
{
    use ControllerTemplateTrait;

    /**
     * init modules template path
     */
    public function __construct()
    {
        parent::__construct();
        $this->loadModulePath();
    }
}
