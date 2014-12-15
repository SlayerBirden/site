<?php
/**
 * This is a part of Maketok Site. Licensed under GPL 3.0
 * @project site
 * @developer Oleg Kulik slayer.birden@gmail.com maketok.com
 */

namespace Maketok\Mvc\Controller;

/**
 * @codeCoverageIgnore
 */
class AbstractBaseController extends AbstractController
{

    /**
     * init
     * add base template path
     */
    public function __construct()
    {
        $this->addTemplatePath(AR . '/src/base/view');
    }
}
