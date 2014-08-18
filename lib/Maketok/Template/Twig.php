<?php
/**
 * This is a part of Maketok Site. Licensed under GPL 3.0
 * Please do not use for your own profit.
 * @project store
 * @developer Slayer slayer.birden@gmail.com maketok.com
 */
namespace Maketok\Template;

use Symfony\Bridge\Twig\Extension\FormExtension;
use Symfony\Bridge\Twig\Form\TwigRenderer;
use Symfony\Bridge\Twig\Form\TwigRendererEngine;

class Twig extends AbstractEngine
{

    const CACHE_FOLDER = 'var/cache';

    /** @var object loaded template */
    protected $_template;

    /**
     * @var array
     */
    protected $_variables = array();

    /**
     * set loader, env
     * @param bool $debug
     */
    public function __construct($debug = false)
    {
        $vendorTwigBridgeDir = AR . DS . 'vendor' . DS .
            'symfony' . DS . 'twig-bridge' . DS .
            'Symfony' . DS . 'Bridge' . DS . 'Twig' . DS . 'Resources' . DS . 'views' . DS . 'Form';
        $loader = new \Twig_Loader_Filesystem(array(
            $vendorTwigBridgeDir,
        ));
        $this->_engine = new \Twig_Environment($loader, array(
            'cache' => AR . DS . self::CACHE_FOLDER,
            'debug' => $debug,
        ));
        $this->_engine->addExtension(new \Twig_Extensions_Extension_I18n());
        $this->_engine->addExtension(new \Twig_Extensions_Extension_Text());
        $defaultFormTheme = 'form_div_layout.html.twig';
        $formEngine = new TwigRendererEngine(array($defaultFormTheme));
        $formEngine->setEnvironment($this->_engine);
        $this->_engine->addExtension(
            new FormExtension(new TwigRenderer($formEngine))
        );
    }

    /**
     * load the template into engine by path
     * @param string $path
     * @return mixed
     */
    public function loadTemplate($path)
    {
        $this->_engine->getLoader()->addPath(dirname($path));
        $this->_template = $this->_engine->loadTemplate(basename($path));
    }

    /**
     * set the array of variables to use
     * @param array $variables
     * @return mixed
     */
    public function setVariables(array $variables)
    {
        $this->_variables = $variables;
    }

    /**
     * return template's content
     * @return string
     */
    public function render()
    {
        return $this->_template->render($this->_variables);
    }

    /**
     * include required paths into loader
     * @param array $paths
     * @return mixed
     */
    public function loadDependencies(array $paths)
    {
        if (isset($this->_engine)) {
            foreach ($paths as $path) {
                $this->_engine->getLoader()->addPath($path);
            }
        }
    }
}