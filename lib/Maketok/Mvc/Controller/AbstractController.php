<?php
/**
 * This is a part of Maketok Site. Licensed under GPL 3.0
 * Please do not use for your own profit.
 * @project store
 * @developer Slayer slayer.birden@gmail.com maketok.com
 */

namespace Maketok\Mvc\Controller;

use Maketok\App\Site;
use Maketok\Http\Response;
use Maketok\Mvc\GenericException;
use Maketok\Util\RequestInterface;
use Maketok\Template;
use Symfony\Component\HttpFoundation\RedirectResponse;

class AbstractController
{

    /** @var  Response */
    protected $_response;

    /** @var  string */
    protected $_body;

    /** @var  string */
    protected $_module;

    /** @var  string */
    protected $_template;

    /** @var  array - dependencies for configs */
    protected $_dependency = array();

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
     * @throws GenericException
     */
    protected function _initResponse($content, $code, $headers)
    {
        if (is_null($content) && !is_null($this->_body)) {
            $content = $this->_body;
        } elseif (is_null($content) && is_null($this->_body)) {
            throw new GenericException("Can't initiate Response object without any body.");
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
     * @param int $httpCode
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function prepareResponse(RequestInterface $request, array $templateVars, array $params = null, $httpCode = 200)
    {
        if (is_null($this->_response)) {
            $this->prepareContent($templateVars, $params);
            $this->_initResponse($this->getContent(), $httpCode, array());
        }
        return $this->_response->prepare($request);
    }

    /**
     * @param array $templateVars
     * @param array $params
     * @return void
     */
    public function prepareContent(array $templateVars, array $params = null)
    {
        $path = $this->_getTemplatePath();
        // get template Engine
        $engine = $this->getSC()->get('template_engine');
        $dependencyPaths = array();
        foreach ($this->_dependency as $_dependencyModule) {
            $dependencyPaths[] = $this->_getTemplatePath('', $_dependencyModule);
        }
        // now add general variables
        $templateVars['css_url'] = Site::getUrl('/css/');
        $templateVars['js_url'] = Site::getUrl('/js/');
        $templateVars['base_url'] = Site::getBaseUrl();
        $engine->loadDependencies($dependencyPaths);
        $engine->loadTemplate($path);
        $engine->setVariables($templateVars);
        $this->_body = $engine->render();
    }

    /**
     * @param null $template
     * @param string|null $module
     * @throws GenericException
     * @return string
     */
    protected function _getTemplatePath($template = null, $module = null)
    {
        if (is_null($module)) {
            $module = $this->_module;
        }
        if (is_null($template)) {
            $template = $this->_template;
        }
        if (is_null($this->_template)) {
            throw new GenericException("Can't find template path, no template set.");
        }
        return AR . DS . 'modules' . DS . $module . DS . 'view' . DS . $template;
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
     * @param array $moduleNames
     * @return $this
     */
    public function setDependency(array $moduleNames)
    {
        $this->_dependency = $moduleNames;
        return $this;
    }

    /**
     * @return string
     */
    public function getTemplate()
    {
        return $this->_template;
    }

    /**
     * @return array
     */
    public function getDependency()
    {
        return $this->_dependency;
    }

    /**
     * @return \Symfony\Component\DependencyInjection\ContainerBuilder
     */
    public function getSC()
    {
        return Site::getServiceContainer();
    }

    /**
     * @return \Symfony\Component\Form\FormFactoryInterface
     */
    public function getFormFactory()
    {
        return $this->getSC()->get('form_builder')
            ->getFormFactory();
    }

    /**
     * @param string $url
     * @return RedirectResponse
     */
    protected function _redirectUrl($url)
    {
       return new RedirectResponse($url);
    }

    /**
     * @param string $path
     * @return RedirectResponse
     */
    protected function _redirect($path)
    {
        $url = Site::getUrl($path);
        return $this->_redirectUrl($url);
    }

    /**
     * @return RedirectResponse
     */
    protected function _returnBack()
    {
        $referer = Site::getRequest()->headers->get('referer');
        return $this->_redirectUrl($referer);
    }

    /**
     * @param string $path
     * @return string
     */
    public function getUrl($path)
    {
        return Site::getUrl($path);
    }
}
