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
use Symfony\Component\HttpFoundation\Request;

class Literal implements RouteInterface
{


    /** @var  string */
    protected $_matchPath;

    public function __construct($path) {
        $this->setPath($path);
    }

    /**
     * @param Request $request
     * @return bool
     */
    public function match(RequestInterface $request)
    {
        if ($request->getPathInfo() === $this->_matchPath) {
            return new Success($this);
        }
        return false;
    }

    /**
     * @param array $params
     * @return string
     */
    public function assemble(array $params)
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
}