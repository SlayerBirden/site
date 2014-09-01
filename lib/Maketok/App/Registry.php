<?php
/**
 * This is a part of Maketok Site. Licensed under GPL 3.0
 * Please do not use for your own profit.
 * @project site
 * @developer Slayer slayer.birden@gmail.com maketok.com
 */

namespace Maketok\App;

use Maketok\App\Exception\AppException;

final class Registry
{
    private $_keys = array();

    /**
     * @var Registry
     */
    private static $_instance;

    public function __get($key)
    {
        if (isset($this->_keys[$key])) {
            return $this->_keys[$key];
        }
        return null;
    }

    /**
     * @param string $key
     * @param mixed $value
     * @throws Exception\AppException
     */
    public function __set($key, $value)
    {
        if (isset($this->_keys[$key])) {
            throw new AppException(sprintf("Can not set existing value to the registry for %s.", $key));
        }
        $this->_keys[$key] = $value;
    }

    public function __unset($key)
    {
        if (isset($this->_keys[$key])) {
            unset($this->_keys[$key]);
        }
    }

    /**
     * @return Registry
     */
    public static function getInstance()
    {
        if (!isset(self::$_instance)) {
            self::$_instance = new Registry();
        }
        return self::$_instance;
    }

    private function __construct()
    {
        return;
    }

    private function __clone()
    {
        return;
    }
}
