<?php
/**
 * This is a part of Maketok Site. Licensed under GPL 3.0
 * Please do not use for your own profit.
 * @project store
 * @developer Slayer slayer.birden@gmail.com maketok.com
 */

namespace Maketok\Http;


use Maketok\Util\RequestInterface;

class Request extends AbstractMessage implements RequestInterface
{


    /** @var  array */
    protected $_params;

    /** @var  array */
    protected $_postParams;

    /** @var  array */
    protected $_getParams;

    /** @var  string */
    protected $_method;

    public static $methods = [
        'OPTIONS' => 'OPTIONS',
        'GET' => 'GET',
        'HEAD' => 'HEAD',
        'POST' => 'POST',
        'PUT' => 'PUT',
        'DELETE' => 'DELETE',
        'TRACE' => 'TRACE',
        'CONNECT' => 'CONNECT',
        'PROPFIND' => 'PROPFIND',
    ];



    /**
     * @return array
     */
    public function getParams()
    {
        return $this->_params;
    }

    /**
     * @return array
     */
    public function getPost()
    {
        return $this->_postParams;
    }

    /**
     * @return array
     */
    public function getGet()
    {
        return $this->_getParams;
    }

    /**
     * @return string
     */
    public function getUriString()
    {

    }

    /**
     * @return string
     */
    public function getQuery()
    {

    }

    /**
     * @return string
     */
    public function getMethod()
    {
        return $this->_method;
    }

    /**
     * @param string $method
     * @throws \Exception
     * @return string
     */
    public function setMethod($method)
    {
        if (!in_array($method, self::$methods)) {
            throw new \Exception(sprintf("Can not set HTTP method %s. Must be one of the valid HTTP methods."));
        }
    }

    /**
     * @return string
     */
    public function getCookie()
    {

    }

    /**
     * @return string
     */
    public function getFiles()
    {

    }

}