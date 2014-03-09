<?php
/**
 * This is a part of Maketok Site. Licensed under GPL 3.0
 * Please do not use for your own profit.
 * @project store
 * @developer Slayer slayer.birden@gmail.com maketok.com
 */
namespace Maketok\Observer;

class State
{
    private $_data = array();

    public function __set($key, $value)
    {
        $this->_data[$key] = $value;
    }

    public function __get($key)
    {
        return $this->_data[$key];
    }
}