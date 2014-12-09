<?php
/**
 * This is a part of Maketok Site. Licensed under GPL 3.0
 *
 * @project site
 * @developer Slayer slayer.birden@gmail.com maketok.com
 */

namespace Maketok\Mvc\Router\Route\Http;


use Maketok\Mvc\Router\Route\RouteInterface;
use Maketok\Mvc\Router\Route\Success;
use Maketok\Util\RequestInterface;
use Symfony\Component\HttpFoundation\ParameterBag;

class Error extends AbstractRoute implements RouteInterface
{

    public function __construct($parameters)
    {
        $this->_parameters = $parameters;
    }

    /**
     * @param RequestInterface $request
     * @return Success
     */
    public function match(RequestInterface $request)
    {
        $this->_request = $request;
        $params = array(
            '_route' => $this,
        );
        if (isset($this->_parameters['exception'])) {
            $params['exception'] = $this->_parameters['exception'];
        }
        $attributes = $request->getAttributes();
        if (is_object($attributes) && ($attributes instanceof ParameterBag)) {
            $attributes->add($params);
        } elseif (is_array($attributes)) {
            $attributes[] = $params;
        }
        return new Success($this);
    }

    /**
     * @param array $params
     * @return string
     */
    public function assemble(array $params = array())
    {
        return '';
    }

    /**
     * @return RequestInterface
     */
    public function getRequest()
    {
        return $this->_request;
    }

    /**
     * @return array
     */
    public function getParameters()
    {
        return $this->_parameters;
    }
}
