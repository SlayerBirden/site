<?php
/**
 * This is a part of Maketok Site. Licensed under GPL 3.0
 * Please do not use for your own profit.
 * @project store
 * @developer Slayer slayer.birden@gmail.com maketok.com
 */

namespace Maketok\Mvc\Router\Route\Http;

abstract class AbstractRoute
{

    /**
     * this function is created to make sure there is no trailing-slash-error cases
     * such as when user types url with trailing slash,
     * and the route is set up without
     *
     * @param $string
     * @return string
     */
    public function stripTrailingSlash($string)
    {
        return rtrim($string, '/');
    }
}
