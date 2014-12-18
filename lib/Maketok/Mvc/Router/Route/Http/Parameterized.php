<?php
/**
 * This is a part of Maketok Site. Licensed under GPL 3.0
 * @project site
 * @developer Oleg Kulik slayer.birden@gmail.com maketok.com
 */

namespace Maketok\Mvc\Router\Route\Http;


use Maketok\Mvc\Router\Route\RouteInterface;
use Maketok\Mvc\Router\Route\Success;
use Maketok\Util\RequestInterface;
use Maketok\Util\ExpressionParser;
use Maketok\Util\ExpressionParserInterface;
use Maketok\Util\Tokenizer;
use Symfony\Component\HttpFoundation\ParameterBag;

class Parameterized extends AbstractRoute implements RouteInterface
{

    /** @var  ExpressionParserInterface */
    protected $expressionParser;

    /** @var  array */
    protected $variables;

    /** @var  array */
    protected $defaults;

    /** @var  array */
    protected $restrictions;

    /**
     * @param string $path
     * @param array $resolver
     * @param array $defaults
     * @param array $restrictions
     * @param \Maketok\Util\ExpressionParserInterface $parser
     */
    public function __construct($path, $resolver, array $defaults, array $restrictions, ExpressionParserInterface $parser = null)
    {
        $this->setPath($path);
        $this->resolver = $resolver;
        $this->defaults = $defaults;
        $this->restrictions = $restrictions;
        if (is_null($parser)) {
            $this->expressionParser = new ExpressionParser($this->matchPath, new Tokenizer($this->matchPath));
        } else {
            $this->expressionParser = $parser;
        }
    }

    /**
     * @param RequestInterface $request
     * @return bool|Success
     */
    public function match(RequestInterface $request)
    {
        $this->request = $request;
        $this->variables = $this->expressionParser->parse(
            $this->stripTrailingSlash($request->getPathInfo()),
            $this->restrictions
        );
        if ($this->variables !== FALSE) {
            $attributes = $request->getAttributes();
            if (is_object($attributes) && ($attributes instanceof ParameterBag)) {
                $attributes->add(array(
                    '_route' => $this,
                ));
                // set defaults
                if (!empty($this->defaults)) {
                    $attributes->add($this->defaults);
                }
                // set variables
                if (!empty($this->variables)) {
                    $attributes->add($this->variables);
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
        // defaults
        $parameters = $this->defaults;
        $parameters = array_replace($parameters, $this->variables, $params);

        return $this->expressionParser->evaluate($parameters, $this->restrictions);
    }
}
