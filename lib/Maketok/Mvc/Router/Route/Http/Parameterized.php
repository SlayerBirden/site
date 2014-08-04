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
use Maketok\Util\ExpressionParser;
use Maketok\Util\ExpressionParserInterface;
use Symfony\Component\HttpFoundation\ParameterBag;

class Parameterized implements RouteInterface
{

    /** @var  string */
    protected $_matchPath;

    /** @var  ExpressionParserInterface */
    protected $_expressionParser;

    /** @var  array */
    protected $_parameters;

    /** @var  array */
    protected $_variables;

    /** @var  array */
    protected $_defaults;

    /** @var  array */
    protected $_restrictions;

    /** @var  RequestInterface */
    protected $_request;

    /**
     * @param $path
     * @param array $parameters
     * @param array $defaults
     * @param array $restrictions
     * @param \Maketok\Util\ExpressionParserInterface $parser
     */
    public function __construct($path, array $parameters, array $defaults, array $restrictions, ExpressionParserInterface $parser = null) {
        $this->setPath($path);
        $this->_parameters = $parameters;
        $this->_defaults = $defaults;
        $this->_restrictions = $restrictions;
        if (is_null($parser)) {
            $this->_expressionParser = new ExpressionParser($this->_matchPath);
        } else {
            $this->_expressionParser = $parser;
        }
    }

    /**
     * @param RequestInterface $request
     * @return bool|Success
     */
    public function match(RequestInterface $request)
    {
        $this->_request = $request;
        if ($variables = $this->_expressionParser->parse($request->getPathInfo(), $this->_restrictions)) {
            // set defaults
            if (is_object($request->attributes) &&
                ($request->attributes instanceof ParameterBag) &&
                !empty($this->_defaults)) {
                $request->attributes->add($this->_defaults);
            }
            // set variables
            $this->_variables = $variables;
            if (is_object($request->attributes) &&
                ($request->attributes instanceof ParameterBag) &&
                !empty($variables)) {
                $request->attributes->add($variables);
            }
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
        // defaults
        $parameters = $this->_defaults;
        $parameters = array_replace($parameters, $this->_variables);

        return $this->_expressionParser->evaluate($parameters, $this->_restrictions);
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