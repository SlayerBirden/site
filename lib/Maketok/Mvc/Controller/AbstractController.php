<?php
/**
 * This is a part of Maketok Site. Licensed under GPL 3.0
 * Please do not use for your own profit.
 * @project store
 * @developer Slayer slayer.birden@gmail.com maketok.com
 */

namespace Maketok\Mvc\Controller;

use Maketok\Http\Response;
use Maketok\Util\RequestInterface;

class AbstractController {

    /** @var  Response */
    protected $_response;

    /** @var  string */
    protected $_body;

    /** @var  string */
    protected $_module;

    /** @var  string */
    protected $_template;

    /**
     * so some magic
     */
    public function __construct()
    {
        $className = get_called_class();
        $classNameSplitted = explode('\\', $className);
        if (($key = array_search('modules', $classNameSplitted)) !== false) {
            $key++;
            $this->_module = $classNameSplitted[$key];
        }
    }

    /**
     * @param string|null $content
     * @param int $code
     * @param array|null $headers
     * @return Response
     */
    public function getResponse($content = null, $code = 200, array $headers = null)
    {
        if (is_null($this->_response)) {
            $this->_initResponse($content, $code, $headers);
        }
        return $this->_response;
    }

    /**
     * @param string|null $content
     * @param int $code
     * @param array|null $headers
     * @throws \Exception
     */
    protected function _initResponse($content, $code, $headers)
    {
        if (is_null($content) && !is_null($this->_body)) {
            $content = $this->_body;
        } elseif (is_null($content) && is_null($this->_body)) {
            throw new \Exception("Can't initiate Response object without any body.");
        }
        if (is_null($headers)) {
            $headers = array();
        }
        $this->_response = Response::create($content, $code, $headers);
    }

    /**
     * @param RequestInterface $request
     * @param array $templateVars
     * @param array $params
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function prepareResponse(RequestInterface $request, array $templateVars, array $params = null)
    {
        if (is_null($this->_response)) {
            $this->prepareContent($templateVars, $params);
            $this->_initResponse($this->getContent(), 200, array());
        }
        return $this->_response->prepare($request);
    }

    /**
     * @param array $templateVars
     * @param array $params
     * @throws \Exception
     * @return void
     */
    public function prepareContent(array $templateVars, array $params = null)
    {
        $path = $this->_getTemplatePath();
        $template = new \PHPTAL($path);
        foreach ($templateVars as $key => $value) {
            $template->$key = $value;
        }
        $this->_body = $template->execute();
    }

    /**
     * @throws \Exception
     * @return string
     */
    protected function _getTemplatePath()
    {
        if (is_null($this->_template)) {
            throw new \Exception("Can't find template path, no template set.");
        }
        return APPLICATION_ROOT . "/modules/{$this->_module}/view/{$this->_template}";
    }

    /**
     * @return string
     */
    public function getContent()
    {
        return $this->_body;
    }

    /**
     * @param string $template
     * @return $this
     */
    public function setTemplate($template)
    {
        $this->_template = $template;
        return $this;
    }

    /**
     * @return string
     */
    public function getTemplate()
    {
        return $this->_template;
    }
} 