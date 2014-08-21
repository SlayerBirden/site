<?php
/**
 * This is a part of Maketok Site. Licensed under GPL 3.0
 * Please do not use for your own profit.
 * @project store
 * @developer Slayer slayer.birden@gmail.com maketok.com
 */

namespace Maketok\Mvc\Router\Route\Http;


use Maketok\Mvc\Router\Route\RouteInterface;
use Maketok\Mvc\Router\Route\Success;
use Maketok\Util\RequestInterface;
use Symfony\Component\HttpFoundation\ParameterBag;

class Literal extends AbstractRoute implements RouteInterface
{


    /** @var  string */
    protected $_matchPath;

    /** @var  array */
    protected $_parameters;

    /** @var  array */
    protected $_defaults;

    /** @var  RequestInterface */
    protected $_request;

    /**
     * @param $path
     * @param array $parameters
     * @param array $defaults
     */
    public function __construct($path, array $parameters,  array $defaults = array()) {
        $this->setPath($path);
        $this->_parameters = $parameters;
        $this->_defaults = $defaults;
    }

    /**
     * @param RequestInterface $request
     * @return bool|Success
     */
    public function match(RequestInterface $request)
    {
        $this->_request = $request;
        if ($this->stripTrailingSlash($request->getPathInfo()) === $this->stripTrailingSlash($this->_matchPath)) {
            if (is_object($request->attributes) && ($request->attributes instanceof ParameterBag)) {
                $request->attributes->add(array(
                    '_route' => $this,
                ));
                if (!empty($this->_defaults)) {
                    $request->attributes->add($this->_defaults);
                }
            }
            return new Success($this);
        }
        return false;
    }

    /**
     * @param array $params
     * @return string
     */
    public function assemble(array $params = array())
    {
        return $this->_matchPath;
    }

    /**
     * @param string $path
     * @return $this
     */
    public function setPath($path)
    {
        $this->_matchPath = $path;
        return $this;
    }

    /**
     * @return array
     */
    public function getParameters()
    {
        return $this->_parameters;
    }

    /**
     * @return RequestInterface
     */
    public function getRequest()
    {
        return $this->_request;
    }
}
