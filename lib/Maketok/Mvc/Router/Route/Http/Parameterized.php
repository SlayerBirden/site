<?php
/**
 * This is a part of Maketok Site. Licensed under GPL 3.0
 * Please do not use for your own profit.
 * @project site
 * @developer Slayer slayer.birden@gmail.com maketok.com
 */

namespace Maketok\Mvc\Router\Route\Http;


use Maketok\Mvc\Router\Route\RouteInterface;
use Maketok\Mvc\Router\Route\Success;
use Maketok\Util\RequestInterface;
use Maketok\Util\ExpressionParser;
use Maketok\Util\ExpressionParserInterface;
use Symfony\Component\HttpFoundation\ParameterBag;

class Parameterized extends AbstractRoute implements RouteInterface
{

    /** @var  string */
    protected $_matchPath;

    /** @var  ExpressionParserInterface */
    protected $_expressionParser;

    /** @var  array */
    protected $_variables;

    /** @var  array */
    protected $_defaults;

    /** @var  array */
    protected $_restrictions;

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
        $variables = $this->_expressionParser->parse(
            $this->stripTrailingSlash($request->getPathInfo()),
            $this->_restrictions);
        if ($variables !== FALSE) {
            $attributes = $request->getAttributes();
            // set defaults
            if (is_object($attributes) && ($attributes instanceof ParameterBag)) {
                $attributes->add(array(
                    '_route' => $this,
                ));
                if (!empty($this->_defaults)) {
                    $attributes->add($this->_defaults);
                }
            } elseif (is_array($attributes)) {
                $attributes[] = ['_route' => $this];
                if (!empty($this->_defaults)) {
                    $attributes[] = $this->_defaults;
                }
            }
            // set variables
            $this->_variables = $variables;
            if (is_object($attributes) &&
                ($attributes instanceof ParameterBag) &&
                !empty($variables)) {
                $attributes->add($variables);
            } elseif (is_array($attributes)) {
                $attributes[] = $variables;
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
        // defaults
        $parameters = $this->_defaults;
        $parameters = array_replace($parameters, $this->_variables, $params);

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
