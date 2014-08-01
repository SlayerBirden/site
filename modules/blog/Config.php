<?php
/**
 * This is a part of Maketok Site. Licensed under GPL 3.0
 * Please do not use for your own profit.
 * @project store
 * @developer Slayer slayer.birden@gmail.com maketok.com
 */

namespace modules\blog;


use Maketok\App\Site;
use Maketok\Module\ConfigInterface;
use Maketok\Mvc\Router\Route\Http\Literal;

class Config implements ConfigInterface
{

    /**
     * @return array
     */
    public static function getDdlConfig()
    {
        return include 'config/ddl/' . self::getDdlConfigVersion() . '.php';
    }

    /**
     * @return string
     */
    public static function getDdlConfigVersion()
    {
        return '0.1.0';
    }

    /**
     * @return string
     */
    public static function getDdlConfigName()
    {
        return 'blog';
    }

    public function initRoutes()
    {
        Site::getCurrentRouter()->addRoute(new Literal('blog', array(
            'module' => $this->getCode(),
            'controller' => 'modules\\blog\\controller\\Index',
            'action' => 'index',
        )));
    }

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
        return 'blog';
    }

    /**
     * magic method for returning string representation of the the config class
     * @return string
     */
    public function __toString()
    {
        return $this->getCode();
    }
}