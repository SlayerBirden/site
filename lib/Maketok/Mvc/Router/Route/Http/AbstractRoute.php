<?php
/**
 * This is a part of Maketok Site. Licensed under GPL 3.0
 * @project site
 * @developer Oleg Kulik slayer.birden@gmail.com maketok.com
 */

namespace Maketok\Mvc\Router\Route\Http;

use Maketok\Util\RequestInterface;

abstract class AbstractRoute
{

    /** @var  RequestInterface */
    protected $_request;

    /** @var  array */
    protected $_parameters;

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
