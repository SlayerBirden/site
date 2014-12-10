<?php
/**
 * This is a part of Maketok Site. Licensed under GPL 3.0
 *
 * @project site
 * @developer Oleg Kulik slayer.birden@gmail.com maketok.com
 */

namespace Maketok\Util;


trait ArrayValue
{

    /**
     * @param string|string[] $key
     * @param array $data
     * @param null|mixed $default
     * @return mixed|null
     */
    public function getIfExists($key, array $data, $default = null)
    {
        if (is_array($key)) {
            while (!is_null($simpleKey = array_shift($key)) && is_array($data)) {
                $data = $this->getIfExists($simpleKey, $data, $default);
            }
            return $data;
        } else {
            if (array_key_exists($key, $data)) {
                return $data[$key];
            }
            return $default;
        }
    }
} 
