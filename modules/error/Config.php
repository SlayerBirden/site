<?php
/**
 * This is a part of Maketok Site. Licensed under GPL 3.0
 * Please do not use for your own profit.
 * @project site
 * @developer Slayer slayer.birden@gmail.com maketok.com
 */

namespace modules\error;


use Maketok\App\Site;
use Maketok\Module\ConfigInterface;
use Maketok\Mvc\Router\Route\Http\Parameterized;

class Config implements ConfigInterface
{

    /**
     * @return string
     */
    public function getVersion()
    {
        return '0.1.0';
    }

    /**
     * {@inheritdoc}
     */
    public function initRoutes()
    {
        return;
    }

    /**
     * @internal param StateInterface $state
     */
    public function initNoRoute()
    {
        Site::getServiceContainer()->get('router')->addRoute(new Parameterized('/{anything}', array(
            'module' => $this->getCode(),
            'controller' => 'modules\\error\\controller\\Index',
            'action' => 'noroute',
        ), [], []));
    }

    /**
     * @return bool
     */
    public function isActive()
    {
        return true;
    }

    /**
     * @return string
     */
    public function getCode()
    {
        return 'error';
    }

    /**
     * magic method for returning string representation of the the config class
     * @return string
     */
    public function __toString()
    {
        return $this->getCode();
    }

    /**
     * {@inheritdoc}
     */
    public function initListeners()
    {
        // this is a special case;
        // attaching routes after all other modules are processes
        // we need to catch only unmatched ones
        Site::getSC()->get('subject_manager')->attach(
            'modulemanager_init_listeners_after',
            array($this, 'initNoRoute'), 1);
    }
}
