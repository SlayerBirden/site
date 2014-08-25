<?php
/**
 * This is a part of Maketok Site. Licensed under GPL 3.0
 * Please do not use for your own profit.
 * @project store
 * @developer Slayer slayer.birden@gmail.com maketok.com
 */

namespace modules\base;


use Maketok\App\Site;
use Maketok\Module\ConfigInterface;
use Maketok\Mvc\Router\Route\Http\Literal;

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
        Site::getServiceContainer()->get('router')->addRoute(new Literal('/', array(
            'module' => $this->getCode(),
            'controller' => 'modules\\base\\controller\\Index',
            'action' => 'index',
        )));
    }

    /**
     * {@inheritdoc}
     */
    public function initListeners()
    {
        return;
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
        return 'base';
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
     * some init work before other init processes (events and routes)
     * @return mixed
     */
    public function initBefore()
    {
        return;
    }
}
