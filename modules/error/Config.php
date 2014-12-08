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
use modules\error\controller\Index;

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
        Site::getSC()->get('front_controller')->addDumper(new Index);
    }
}
