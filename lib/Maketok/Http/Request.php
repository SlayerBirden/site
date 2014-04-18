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
    protected $_post;

    /** @var  array */
    protected $_query;

    /** @var  string */
    protected $_method;

    /** @var  array */
    protected $_cookies;

    /** @var  array */
    protected $_files;

    /** @var  string */
    protected $_uri;

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
     * @param $params
     * @return array
     */
    public function setParams($params)
    {
        $this->_params = $params;
        return $this;
    }

    /**
     * @return array
     */
    public function getPost()
    {
        return $this->_post;
    }

    /**
     * @param $post
     * @return $this
     */
    public function setPost($post)
    {
        $this->_post = $post;
        return $this;
    }

    /**
     * @return string
     */
    public function getUriString()
    {
        return $this->_uri;
    }

    /**
     * @param $uri
     * @return $this
     */
    public function setUriString($uri)
    {
        $this->_uri = $uri;
        return $this;
    }

    /**
     * @return array
     */
    public function getQuery()
    {
        return $this->_query;
    }

    /**
     * @param array $query
     * @return $this
     */
    public function setQuery($query)
    {
        $this->_query = $query;
        return $this;
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
    public function getCookies()
    {
        return $this->_cookies;
    }

    /**
     * @param $cookies
     * @return $this
     */
    public function setCookies($cookies)
    {
        $this->_cookies = $cookies;
        return $this;
    }

    /**
     * @return string
     */
    public function getFiles()
    {
        return $this->_files;
    }

    /**
     * @param $files
     * @return $this
     */
    public function setFiles($files)
    {
        $this->_files = $files;
        return $this;
    }

}